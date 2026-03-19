# 社交购物订单助手 - 技术实现规范

> **技术栈**：Python + FastAPI + PostgreSQL + Redis + Meta API

---

## 一、系统架构

### 1.1 整体架构图

```
┌─────────────────────────────────────────────┐
│   用户层                                     │
├─────────────────────────────────────────────┤
│  Facebook Messenger  │  Instagram DM        │
└──────────┬──────────────────────────────────┘
           │
           ↓ Webhook
┌─────────────────────────────────────────────┐
│   Meta API 接入层                            │
├─────────────────────────────────────────────┤
│  - Messenger Platform API                   │
│  - Instagram Graph API                      │
│  - Webhook 验证与接收                        │
└──────────┬──────────────────────────────────┘
           │
           ↓ HTTP/JSON
┌─────────────────────────────────────────────┐
│   中间服务层 (FastAPI)                       │
├─────────────────────────────────────────────┤
│  - 消息路由                                  │
│  - 会话管理                                  │
│  - 状态机引擎                                │
│  - 信息提取                                  │
│  - 防混单逻辑                                │
└──────────┬──────────────────────────────────┘
           │
           ↓
┌─────────────────────────────────────────────┐
│   数据层                                     │
├─────────────────────────────────────────────┤
│  PostgreSQL (核心数据)                       │
│  Redis (会话缓存/状态机)                     │
└──────────┬──────────────────────────────────┘
           │
           ↓ API
┌─────────────────────────────────────────────┐
│   现有物流系统 (ThinkPHP)                    │
├─────────────────────────────────────────────┤
│  - Customer (用户)                           │
│  - Package (包裹预报)                        │
│  - Inpack (集运订单-后续打包)                │
│  - Statement (账单)                          │
└─────────────────────────────────────────────┘
```

**核心概念澄清**：
- **包裹号（express_num）**：用户从卖家处获得的国际快递单号，这是用户在机器人中提供的"单号"
- **集运订单（inpack）**：仓库打包后生成，一个集运订单包含多个包裹
- Bot 系统只处理包裹预报，不涉及集运订单的创建

### 1.2 技术栈详细

| 层级 | 技术 | 用途 |
|------|------|------|
| **API接入** | Meta Graph API | FB/IG 消息收发 |
| **后端框架** | FastAPI | 高性能异步API |
| **核心数据库** | PostgreSQL 14+ | 订单会话、绑定关系 |
| **缓存/状态** | Redis 7+ | 会话状态机、临时数据 |
| **消息队列** | Redis Streams | 异步任务处理 |
| **部署** | Docker + Nginx | 容器化部署 |

---

## 二、数据库设计

### 2.1 核心表结构

#### 表1：platform_account (平台账号)

```sql
CREATE TABLE platform_account (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    platform VARCHAR(20) NOT NULL,  -- 'facebook' / 'instagram'
    platform_user_id VARCHAR(100) NOT NULL,
    platform_username VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(platform, platform_user_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_platform_user (platform, platform_user_id)
);
```

#### 表2：order_session (订单会话 - 核心)

```sql
CREATE TABLE order_session (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    platform_account_id INTEGER NOT NULL,
    
    -- 状态机
    status VARCHAR(20) NOT NULL DEFAULT 'collecting',
    -- collecting / ready / bound / closed
    
    -- 订单信息
    seller_name VARCHAR(255),
    buy_date DATE,
    item_desc TEXT,
    amount DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'VND',
    seller_order_no VARCHAR(100),
    
    -- 元数据
    session_key VARCHAR(50) UNIQUE,  -- 用于Redis关联
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP,
    
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status),
    INDEX idx_session_key (session_key)
);
```

#### 表3：order_package (订单包裹关联)

```sql
CREATE TABLE order_package (
    id SERIAL PRIMARY KEY,
    order_session_id INTEGER NOT NULL,
    package_no VARCHAR(100) NOT NULL,  -- 包裹号（express_num）
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(order_session_id, package_no),
    INDEX idx_order_session (order_session_id),
    INDEX idx_package_no (package_no)
);
```

**说明**：
- `package_no` 存储的是卖家提供的国际快递单号（express_num）
- 一个订单可以有多个包裹号（卖家分批发货）
- 这些包裹号对应集运系统中的 yoshop_package 表

#### 表4：order_message (聊天证据)

```sql
CREATE TABLE order_message (
    id SERIAL PRIMARY KEY,
    order_session_id INTEGER NOT NULL,
    message_type VARCHAR(20) NOT NULL,  -- 'text' / 'image' / 'link'
    content TEXT,
    file_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_order_session (order_session_id)
);
```

### 2.2 Redis 数据结构

#### 会话状态缓存

```python
# Key: session:{platform}:{user_id}
# Value: JSON
{
    "order_session_id": 123,
    "status": "collecting",
    "last_activity": "2024-03-19T10:30:00",
    "pending_info": ["seller_name", "amount"],
    "temp_data": {
        "seller_name": "ABC Store",
        "buy_date": "2024-03-15"
    }
}
# TTL: 24 hours
```

#### 待补订单列表

```python
# Key: pending_orders:{customer_id}
# Value: Sorted Set (按时间排序)
# Score: timestamp
# Member: order_session_id
```

---

## 三、API 设计

### 3.1 Meta Webhook 接收

```python
# POST /webhook/facebook
# POST /webhook/instagram

@app.post("/webhook/{platform}")
async def receive_webhook(
    platform: str,
    request: Request,
    background_tasks: BackgroundTasks
):
    """
    接收 Meta 平台 Webhook 消息
    """
    data = await request.json()
    
    # 验证签名
    if not verify_webhook_signature(request):
        raise HTTPException(401)
    
    # 异步处理消息
    background_tasks.add_task(
        process_message,
        platform=platform,
        data=data
    )
    
    return {"status": "ok"}
```

### 3.2 核心业务接口

```python
# 1. 绑定平台账号
POST /api/v1/account/bind
{
    "platform": "facebook",
    "platform_user_id": "123456",
    "customer_id": "CUST-87231"
}

# 2. 创建订单会话
POST /api/v1/order-session
{
    "customer_id": 123,
    "platform_account_id": 456,
    "seller_name": "ABC Store",
    "buy_date": "2024-03-15",
    "item_desc": "Nike鞋",
    "amount": 1200000,
    "currency": "VND"
}

# 3. 补充包裹号
POST /api/v1/order-session/{id}/add-package
{
    "package_no": "CN123456789"
}

# 4. 获取待补订单列表
GET /api/v1/order-session/pending?customer_id=123

# 5. 更新会话状态
PATCH /api/v1/order-session/{id}/status
{
    "status": "ready"
}
```

---

## 四、核心业务逻辑

### 4.1 消息处理流程

```python
async def process_message(platform: str, data: dict):
    """
    消息处理主流程
    """
    # 1. 提取消息信息
    user_id = extract_user_id(data)
    message_text = extract_message_text(data)
    message_type = extract_message_type(data)
    
    # 2. 获取或创建平台账号
    account = await get_or_create_platform_account(
        platform=platform,
        user_id=user_id
    )
    
    # 3. 检查绑定状态
    if not account.customer_id:
        await send_bind_prompt(platform, user_id)
        return
    
    # 4. 获取当前会话状态
    session_state = await get_session_state(platform, user_id)
    
    # 5. 状态机处理
    await handle_message_by_state(
        account=account,
        session_state=session_state,
        message_text=message_text,
        message_type=message_type
    )
```

### 4.2 状态机引擎

```python
class OrderSessionStateMachine:
    """
    订单会话状态机
    """
    
    async def handle_collecting(self, session, message):
        """
        collecting 状态：收集订单信息
        """
        # 提取信息
        extracted = await extract_order_info(message)
        
        # 更新会话
        await update_session_info(session.id, extracted)
        
        # 检查完整性
        missing = check_missing_info(session)
        
        if not missing:
            # 信息齐全，进入 ready 状态
            await transition_to_ready(session.id)
            await send_message(
                "✅ 订单信息已记录\n等包裹号时发我就行"
            )
        else:
            # 补问缺失信息
            await ask_missing_info(session.id, missing[0])
    
    async def handle_ready(self, session, message):
        """
        ready 状态：等待包裹号
        """
        # 识别包裹号
        package_no = extract_package_number(message)
        
        if package_no:
            # 添加包裹号
            await add_package(session.id, package_no)
            await transition_to_bound(session.id)
            await send_message(
                f"✅ 已记录包裹号 {package_no}"
            )
        else:
            await send_message(
                "请发送包裹号"
            )
    
    async def handle_bound(self, session, message):
        """
        bound 状态：已绑定，可查询
        """
        # 提供查询入口
        await send_message(
            "订单已完成\n可在物流系统查看"
        )
```

### 4.3 信息提取引擎

```python
import re
from datetime import datetime

class InfoExtractor:
    """
    信息提取器
    """
    
    def extract_amount(self, text: str) -> tuple:
        """
        提取金额
        """
        patterns = [
            r'(\d+[,.]?\d*)\s*(k|đ|vnd)',  # 1200k, 1.2tr
            r'(\d+[,.]?\d*)\s*tr',          # 1.2tr
            r'\$\s*(\d+[,.]?\d*)',          # $89.99
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text, re.IGNORECASE)
            if match:
                amount = float(match.group(1).replace(',', ''))
                currency = self._detect_currency(match.group(0))
                return (amount, currency)
        
        return (None, None)
    
    def extract_date(self, text: str) -> datetime:
        """
        提取日期
        """
        patterns = [
            r'(\d{1,2})[/\-](\d{1,2})',     # 3/15, 3-15
            r'(\d{1,2})\s*月\s*(\d{1,2})',  # 3月15日
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text)
            if match:
                month = int(match.group(1))
                day = int(match.group(2))
                year = datetime.now().year
                
                # 跨年智能回退
                date = datetime(year, month, day)
                if date > datetime.now():
                    date = datetime(year - 1, month, day)
                
                return date
        
        return None
    
    def extract_package_number(self, text: str) -> str:
        """
        提取包裹号（国际快递单号）
        """
        patterns = [
            r'[A-Z]{2}\d{9}[A-Z]{2}',  # 国际单号
            r'\d{10,20}',               # 纯数字
            r'[A-Z0-9]{10,30}',         # 混合
        ]
        
        for pattern in patterns:
            match = re.search(pattern, text)
            if match:
                return match.group(0)
        
        return None
```

### 4.4 防混单逻辑

```python
class AntiMixingEngine:
    """
    防混单引擎
    """
    
    async def should_create_new_session(
        self,
        customer_id: int,
        current_session: dict,
        new_message: dict
    ) -> bool:
        """
        判断是否应该创建新会话
        """
        # 规则1：超过24小时
        if self._is_timeout(current_session):
            return True
        
        # 规则2：明确说"新的一单"
        if self._is_explicit_new_order(new_message):
            return True
        
        # 规则3：卖家不同
        new_seller = extract_seller_name(new_message)
        if new_seller and new_seller != current_session.get('seller_name'):
            # 弹出确认按钮
            await self._ask_confirmation(customer_id)
            return None  # 等待用户确认
        
        # 规则4：金额差异大
        new_amount = extract_amount(new_message)
        if new_amount and abs(new_amount - current_session.get('amount', 0)) > 500000:
            await self._ask_confirmation(customer_id)
            return None
        
        return False
    
    def _is_timeout(self, session: dict) -> bool:
        """
        检查是否超时
        """
        last_activity = session.get('last_activity')
        if not last_activity:
            return False
        
        delta = datetime.now() - datetime.fromisoformat(last_activity)
        return delta.total_seconds() > 24 * 3600
```

---

## 五、Meta API 集成

### 5.1 Messenger Platform

```python
import httpx

class MessengerAPI:
    """
    Facebook Messenger API 封装
    """
    
    def __init__(self, page_access_token: str):
        self.token = page_access_token
        self.base_url = "https://graph.facebook.com/v18.0"
    
    async def send_text(self, recipient_id: str, text: str):
        """
        发送文本消息
        """
        url = f"{self.base_url}/me/messages"
        payload = {
            "recipient": {"id": recipient_id},
            "message": {"text": text}
        }
        
        async with httpx.AsyncClient() as client:
            response = await client.post(
                url,
                params={"access_token": self.token},
                json=payload
            )
            return response.json()
    
    async def send_quick_replies(
        self,
        recipient_id: str,
        text: str,
        replies: list
    ):
        """
        发送快速回复按钮
        """
        url = f"{self.base_url}/me/messages"
        payload = {
            "recipient": {"id": recipient_id},
            "message": {
                "text": text,
                "quick_replies": [
                    {
                        "content_type": "text",
                        "title": reply["title"],
                        "payload": reply["payload"]
                    }
                    for reply in replies
                ]
            }
        }
        
        async with httpx.AsyncClient() as client:
            response = await client.post(
                url,
                params={"access_token": self.token},
                json=payload
            )
            return response.json()
```

### 5.2 Instagram Graph API

```python
class InstagramAPI:
    """
    Instagram Graph API 封装
    """
    
    def __init__(self, page_access_token: str):
        self.token = page_access_token
        self.base_url = "https://graph.facebook.com/v18.0"
    
    async def send_message(
        self,
        recipient_id: str,
        text: str
    ):
        """
        发送消息
        """
        url = f"{self.base_url}/me/messages"
        payload = {
            "recipient": {"id": recipient_id},
            "message": {"text": text}
        }
        
        async with httpx.AsyncClient() as client:
            response = await client.post(
                url,
                params={"access_token": self.token},
                json=payload
            )
            return response.json()
```

---

## 六、Quick Reply 配置

### 6.1 场景1：新消息接收

```python
QUICK_REPLY_NEW_MESSAGE = {
    "text": "📦 已收到订单信息\n请选择：",
    "replies": [
        {
            "title": "🆕 新的一单",
            "payload": "NEW_ORDER"
        },
        {
            "title": "🔁 补充之前订单",
            "payload": "CONTINUE_ORDER"
        }
    ]
}
```

### 6.2 场景2：防混单确认

```python
QUICK_REPLY_ANTI_MIXING = {
    "text": "这是同一单吗？",
    "replies": [
        {
            "title": "✔ 同一单",
            "payload": "SAME_ORDER"
        },
        {
            "title": "➕ 新的一单",
            "payload": "NEW_ORDER"
        }
    ]
}
```

### 6.3 场景3：补包裹号

```python
async def generate_pending_orders_reply(customer_id: int):
    """
    动态生成待补订单列表
    """
    orders = await get_pending_orders(customer_id)
    
    replies = []
    for order in orders[:3]:  # 最多3个
        title = f"{order.item_desc}｜{order.buy_date.strftime('%m月%d')}｜{order.amount/1000:.0f}k"
        replies.append({
            "title": title[:20],  # 限制长度
            "payload": f"SELECT_ORDER_{order.id}"
        })
    
    return {
        "text": "请选择要补单号的订单",
        "replies": replies
    }
```

---

## 七、错误处理

### 7.1 异常分类

```python
class BotException(Exception):
    """基础异常"""
    pass

class AccountNotBoundException(BotException):
    """账号未绑定"""
    message = "⚠ 请先绑定客户ID"

class CustomerNotFoundException(BotException):
    """客户不存在"""
    message = "❌ 客户ID不存在\n请重新输入"

class PackageDuplicateException(BotException):
    """包裹号重复"""
    message = "⚠ 该包裹号已被预报"

class SystemException(BotException):
    """系统异常"""
    message = "系统有点忙，请稍后再试"
```

### 7.2 全局异常处理

```python
@app.exception_handler(BotException)
async def bot_exception_handler(request: Request, exc: BotException):
    """
    业务异常处理
    """
    # 发送友好提示给用户
    await send_error_message(
        platform=request.state.platform,
        user_id=request.state.user_id,
        message=exc.message
    )
    
    return JSONResponse(
        status_code=200,
        content={"status": "handled"}
    )
```

---

## 八、部署配置

### 8.1 Docker Compose

```yaml
version: '3.8'

services:
  api:
    build: .
    ports:
      - "8000:8000"
    environment:
      - DATABASE_URL=postgresql://user:pass@db:5432/botdb
      - REDIS_URL=redis://redis:6379/0
      - FB_PAGE_TOKEN=${FB_PAGE_TOKEN}
      - IG_PAGE_TOKEN=${IG_PAGE_TOKEN}
    depends_on:
      - db
      - redis
  
  db:
    image: postgres:14
    environment:
      - POSTGRES_DB=botdb
      - POSTGRES_USER=user
      - POSTGRES_PASSWORD=pass
    volumes:
      - pgdata:/var/lib/postgresql/data
  
  redis:
    image: redis:7-alpine
    volumes:
      - redisdata:/data

volumes:
  pgdata:
  redisdata:
```

### 8.2 环境变量

```bash
# .env
DATABASE_URL=postgresql://user:pass@localhost:5432/botdb
REDIS_URL=redis://localhost:6379/0

# Meta API
FB_PAGE_TOKEN=your_facebook_page_token
IG_PAGE_TOKEN=your_instagram_page_token
FB_VERIFY_TOKEN=your_webhook_verify_token

# 物流系统API
LOGISTICS_API_URL=https://your-logistics-system.com/api
LOGISTICS_API_KEY=your_api_key
```

---

## 九、监控与日志

### 9.1 关键指标

```python
from prometheus_client import Counter, Histogram

# 消息处理计数
message_counter = Counter(
    'bot_messages_total',
    'Total messages processed',
    ['platform', 'status']
)

# 会话创建计数
session_counter = Counter(
    'bot_sessions_total',
    'Total sessions created',
    ['platform']
)

# 包裹预报计数
package_counter = Counter(
    'bot_packages_reported',
    'Total packages reported'
)

# 处理延迟
processing_latency = Histogram(
    'bot_processing_seconds',
    'Message processing latency'
)
```

### 9.2 日志规范

```python
import logging

logger = logging.getLogger(__name__)

# 关键操作日志
logger.info(
    "Session created",
    extra={
        "customer_id": customer_id,
        "platform": platform,
        "session_id": session_id
    }
)

# 错误日志
logger.error(
    "Package report failed",
    extra={
        "session_id": session_id,
        "package_no": package_no,
        "error": str(e)
    },
    exc_info=True
)
```

---

## 十、下一步实施计划

### Phase 1: 基础框架（1周）
- ✅ FastAPI 项目搭建
- ✅ PostgreSQL 数据库初始化
- ✅ Redis 连接配置
- ✅ Meta Webhook 接入

### Phase 2: 核心功能（2周）
- ✅ 账号绑定流程
- ✅ 订单会话管理
- ✅ 状态机引擎
- ✅ 信息提取

### Phase 3: 防混单（1周）
- ✅ 防混单逻辑
- ✅ Quick Reply 交互
- ✅ 多订单管理

### Phase 4: 集成测试（1周）
- ✅ 端到端测试
- ✅ 压力测试
- ✅ 异常场景测试

### Phase 5: 上线部署（3天）
- ✅ Docker 部署
- ✅ 监控配置
- ✅ 灰度发布

---

**总计：约 5-6 周完成 MVP**



---

## 附录A：完整代码示例

### A.1 FastAPI 主应用

```python
# main.py
from fastapi import FastAPI, Request, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
import uvicorn

app = FastAPI(title="Social Shopping Bot API")

# CORS 配置
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Webhook 路由
from routers import webhook, api

app.include_router(webhook.router, prefix="/webhook")
app.include_router(api.router, prefix="/api/v1")

@app.get("/health")
async def health_check():
    return {"status": "ok"}

if __name__ == "__main__":
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=8000,
        reload=True
    )
```

### A.2 数据库模型

```python
# models/order_session.py
from sqlalchemy import Column, Integer, String, Numeric, Date, DateTime, Boolean
from sqlalchemy.sql import func
from database import Base

class OrderSession(Base):
    __tablename__ = "order_session"
    
    id = Column(Integer, primary_key=True, index=True)
    customer_id = Column(Integer, nullable=False, index=True)
    platform_account_id = Column(Integer, nullable=False)
    
    # 状态机
    status = Column(String(20), nullable=False, default='collecting', index=True)
    
    # 订单信息
    seller_name = Column(String(255))
    buy_date = Column(Date)
    item_desc = Column(String)
    amount = Column(Numeric(10, 2))
    currency = Column(String(10), default='VND')
    seller_order_no = Column(String(100))
    
    # 元数据
    session_key = Column(String(50), unique=True, index=True)
    created_at = Column(DateTime, server_default=func.now())
    updated_at = Column(DateTime, server_default=func.now(), onupdate=func.now())
    closed_at = Column(DateTime)
```

### A.3 服务层示例

```python
# services/session_service.py
from typing import Optional
from models.order_session import OrderSession
from database import get_db

class SessionService:
    
    async def create_session(
        self,
        customer_id: int,
        platform_account_id: int
    ) -> OrderSession:
        """
        创建新的订单会话
        """
        session = OrderSession(
            customer_id=customer_id,
            platform_account_id=platform_account_id,
            status='collecting',
            session_key=self._generate_session_key()
        )
        
        db = get_db()
        db.add(session)
        db.commit()
        db.refresh(session)
        
        # 缓存到 Redis
        await self._cache_session(session)
        
        return session
    
    async def get_active_session(
        self,
        customer_id: int
    ) -> Optional[OrderSession]:
        """
        获取客户的活跃会话
        """
        # 先查 Redis
        cached = await self._get_cached_session(customer_id)
        if cached:
            return cached
        
        # 查数据库
        db = get_db()
        session = db.query(OrderSession).filter(
            OrderSession.customer_id == customer_id,
            OrderSession.status.in_(['collecting', 'ready'])
        ).order_by(
            OrderSession.updated_at.desc()
        ).first()
        
        if session:
            await self._cache_session(session)
        
        return session
    
    async def update_session_info(
        self,
        session_id: int,
        **kwargs
    ):
        """
        更新会话信息
        """
        db = get_db()
        session = db.query(OrderSession).filter(
            OrderSession.id == session_id
        ).first()
        
        if not session:
            raise ValueError("Session not found")
        
        for key, value in kwargs.items():
            if hasattr(session, key):
                setattr(session, key, value)
        
        db.commit()
        db.refresh(session)
        
        # 更新缓存
        await self._cache_session(session)
        
        return session
    
    async def transition_status(
        self,
        session_id: int,
        new_status: str
    ):
        """
        状态转换
        """
        valid_transitions = {
            'collecting': ['ready', 'closed'],
            'ready': ['bound', 'closed'],
            'bound': ['closed']
        }
        
        db = get_db()
        session = db.query(OrderSession).filter(
            OrderSession.id == session_id
        ).first()
        
        if not session:
            raise ValueError("Session not found")
        
        if new_status not in valid_transitions.get(session.status, []):
            raise ValueError(f"Invalid transition: {session.status} -> {new_status}")
        
        session.status = new_status
        if new_status == 'closed':
            session.closed_at = func.now()
        
        db.commit()
        db.refresh(session)
        
        await self._cache_session(session)
        
        return session
```

---

## 附录B：测试用例

### B.1 单元测试

```python
# tests/test_info_extractor.py
import pytest
from services.info_extractor import InfoExtractor

def test_extract_amount_vnd():
    extractor = InfoExtractor()
    
    # 测试越南盾格式
    amount, currency = extractor.extract_amount("价格是 1200k")
    assert amount == 1200
    assert currency == "VND"
    
    amount, currency = extractor.extract_amount("1.2tr đ")
    assert amount == 1200000
    assert currency == "VND"

def test_extract_date():
    extractor = InfoExtractor()
    
    # 测试日期格式
    date = extractor.extract_date("3月15日买的")
    assert date.month == 3
    assert date.day == 15
    
    date = extractor.extract_date("3/15")
    assert date.month == 3
    assert date.day == 15

def test_extract_package_number():
    extractor = InfoExtractor()
    
    # 测试包裹号格式
    package_no = extractor.extract_package_number("包裹号 CN123456789AB")
    assert package_no == "CN123456789AB"
    
    package_no = extractor.extract_package_number("1234567890")
    assert package_no == "1234567890"
```

### B.2 集成测试

```python
# tests/test_session_flow.py
import pytest
from httpx import AsyncClient
from main import app

@pytest.mark.asyncio
async def test_complete_session_flow():
    """
    测试完整的会话流程
    """
    async with AsyncClient(app=app, base_url="http://test") as client:
        # 1. 创建会话
        response = await client.post(
            "/api/v1/order-session",
            json={
                "customer_id": 1,
                "platform_account_id": 1,
                "seller_name": "Test Store",
                "buy_date": "2024-03-15",
                "item_desc": "Test Item",
                "amount": 1200000,
                "currency": "VND"
            }
        )
        assert response.status_code == 200
        session_id = response.json()["id"]
        
        # 2. 检查状态
        response = await client.get(f"/api/v1/order-session/{session_id}")
        assert response.json()["status"] == "collecting"
        
        # 3. 转换到 ready
        response = await client.patch(
            f"/api/v1/order-session/{session_id}/status",
            json={"status": "ready"}
        )
        assert response.status_code == 200
        
        # 4. 添加包裹号
        response = await client.post(
            f"/api/v1/order-session/{session_id}/add-package",
            json={"package_no": "CN123456789"}
        )
        assert response.status_code == 200
        
        # 5. 验证最终状态
        response = await client.get(f"/api/v1/order-session/{session_id}")
        assert response.json()["status"] == "bound"
```

---

## 附录C：运维手册

### C.1 常见问题排查

#### 问题1：Webhook 收不到消息

**排查步骤**：
1. 检查 Meta 开发者后台 Webhook 配置
2. 验证 Webhook URL 是否可访问
3. 检查 Verify Token 是否正确
4. 查看应用日志：`docker logs bot-api`

#### 问题2：消息处理延迟

**排查步骤**：
1. 检查 Redis 连接：`redis-cli ping`
2. 查看数据库连接池：`SELECT * FROM pg_stat_activity`
3. 检查后台任务队列积压情况
4. 查看 Prometheus 指标

#### 问题3：状态机卡住

**排查步骤**：
1. 查询卡住的会话：
```sql
SELECT * FROM order_session 
WHERE status IN ('collecting', 'ready')
AND updated_at < NOW() - INTERVAL '24 hours';
```
2. 手动修复状态或关闭会话
3. 清理 Redis 缓存

### C.2 数据库维护

#### 定期清理历史数据

```sql
-- 归档6个月前的已关闭会话
INSERT INTO order_session_archive
SELECT * FROM order_session
WHERE status = 'closed'
AND closed_at < NOW() - INTERVAL '6 months';

-- 删除已归档数据
DELETE FROM order_session
WHERE status = 'closed'
AND closed_at < NOW() - INTERVAL '6 months';
```

#### 索引优化

```sql
-- 分析表统计信息
ANALYZE order_session;

-- 重建索引
REINDEX TABLE order_session;
```

### C.3 监控告警

#### Prometheus 告警规则

```yaml
groups:
  - name: bot_alerts
    rules:
      - alert: HighMessageProcessingLatency
        expr: bot_processing_seconds > 5
        for: 5m
        annotations:
          summary: "消息处理延迟过高"
      
      - alert: SessionCreationFailed
        expr: rate(bot_sessions_total{status="failed"}[5m]) > 0.1
        for: 5m
        annotations:
          summary: "会话创建失败率过高"
```

---

## 附录D：API 文档

完整的 API 文档可通过 FastAPI 自动生成：

访问：`http://your-domain/docs`

主要接口列表：

| 接口 | 方法 | 说明 |
|------|------|------|
| `/webhook/facebook` | POST | FB Webhook |
| `/webhook/instagram` | POST | IG Webhook |
| `/api/v1/account/bind` | POST | 绑定账号 |
| `/api/v1/order-session` | POST | 创建会话 |
| `/api/v1/order-session/{id}` | GET | 获取会话 |
| `/api/v1/order-session/{id}/status` | PATCH | 更新状态 |
| `/api/v1/order-session/{id}/add-package` | POST | 添加包裹号 |
| `/api/v1/order-session/pending` | GET | 待补订单 |

---

## 总结

这份技术规范涵盖了：

✅ 完整的系统架构设计
✅ 数据库表结构（PostgreSQL）
✅ Redis 缓存策略
✅ Meta API 集成方案
✅ 核心业务逻辑实现
✅ 状态机引擎设计
✅ 防混单机制
✅ 错误处理规范
✅ 部署配置
✅ 监控与日志
✅ 测试用例
✅ 运维手册

**下一步行动**：
1. 搭建开发环境
2. 初始化数据库
3. 实现核心 API
4. 对接 Meta Webhook
5. 测试与调优

预计 **5-6 周**完成 MVP 版本！
