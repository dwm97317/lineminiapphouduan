# Kế Hoạch Kiểm Thử Hiệu Năng & Bảo Mật Hệ Thống Bot

Tài liệu này định nghĩa các kịch bản kiểm thử phi chức năng (Non-Functional Testing), bao gồm: Hiệu năng (Performance), Xử lý ngoại lệ tầng hạ tầng (Infrastructure Exception Handling) và Bảo mật (Security) dựa trên tài liệu kỹ thuật của dự án.

---

## 1. Kiểm Thử Hiệu Năng (Performance Testing)

| TC ID | Kịch Bản & Yêu Cầu Kỹ Thuật | Các Bước Thực Hiện (Mô phỏng bằng Công cụ) | Kết Quả Mong Đợi (SLA) | Trạng Thái |
|---|---|---|---|---|
| TC_PERF_01 | **Xử lý tin nhắn đồng thời**<br>Mô phỏng 100 người dùng cùng lúc tương tác với Bot. | Sử dụng JMeter / K6 bắn 100 webhook (concurrent requests) vào API nhận tin nhắn của hệ thống. | - Không rớt request nào (0% Error rate).<br>- Thời gian phản hồi (Response time) < 2s. | [ ] |
| TC_PERF_02 | **Kiểm tra tỷ lệ Cache Hit của Redis**<br>Tối ưu hóa tải cho cơ sở dữ liệu. | Dùng script gửi 1000 request đọc cấu hình và tra cứu mã bưu kiện liên tục trong thời gian ngắn. Đo lường qua Redis Monitor/Grafana. | - Tỷ lệ Cache Hit đạt > 80%.<br>- Hệ thống không gọi trực tiếp vào DB cho các dữ liệu ít thay đổi. | [ ] |
| TC_PERF_03 | **Kiểm thử Connection Pool Database**<br>Chống tràn kết nối database. | Bắn dồn dập 200 request API tạo phiên đặt hàng (Insert DB) đồng thời. Theo dõi process list của MySQL. | - MySQL không báo lỗi "Too many connections" hoặc sập tiến trình.<br>- Hàng đợi (Queue) phân bổ ghi DB ổn định. | [ ] |
| TC_PERF_04 | **Xử lý tồn đọng Webhook**<br>Xử lý tin nhắn đến dồn dập khi hệ thống vừa khôi phục. | 1. Tắt tiến trình xử lý tin nhắn của Bot (Worker/Consumer).<br>2. Gửi 500 tin nhắn nhắn tin cho Bot.<br>3. Bật lại tiến trình Worker. | - Hệ thống tự động đẩy 500 tin nhắn cũ vào Queue.<br>- Xử lý bù toàn bộ lượng tin nhắn bị kẹt mà không mất dữ liệu. | [ ] |

---

## 2. Kiểm Thử Tình Huống Ngoại Lệ Hạ Tầng (Exception Handling)

| TC ID | Kịch Bản & Yêu Cầu Kỹ Thuật | Các Bước Thực Hiện (Mô phỏng bằng Công cụ) | Kết Quả Mong Đợi (SLA) | Trạng Thái |
|---|---|---|---|---|
| TC_EXC_01 | **Xử lý timeout Meta API**<br>Mất kết nối với Facebook/Instagram API. | Cấu hình tường lửa chặn kết nối đi tới IP của Meta hoặc Mock API trả về độ trễ > 10s. Khách hàng gửi tin nhắn cho Bot. | - Bot báo lại lỗi mạng và đưa tin nhắn vào hàng đợi Retry tự động.<br>- Không làm gián đoạn việc xử lý cho các khách hàng khác. | [ ] |
| TC_EXC_02 | **Fallback khi API hệ thống vận chuyển sập**<br>Bên giao hàng thứ 3 (GHTK/GHN) bảo trì. | Gửi request check mã bưu kiện, đồng thời Mock API bên vận chuyển trả mã lỗi 500 hoặc Timeout. | - Hệ thống kích hoạt "Fallback logic".<br>- Bot phản hồi thân thiện: "Hệ thống vận chuyển đang bảo trì, vui lòng kiểm tra lại sau". | [ ] |
| TC_EXC_03 | **Khôi phục sau khi mất kết nối Database**<br>Phòng tránh sập hệ thống dây chuyền. | 1. Tắt service MySQL.<br>2. Chạy tính năng truy vấn đơn hàng trên Bot.<br>3. Bật service lại và test lại. | - Khi tắt DB: Bot báo lỗi "Hệ thống bận".<br>- Khi bật DB: Tự kết nối lại thành công, đọc/ghi được ngay lập tức. | [ ] |
| TC_EXC_04 | **Khôi phục sau khi mất kết nối Redis**<br>Mất hệ thống cache tạm thời. | 1. Tắt service Redis.<br>2. Chạy API đọc cấu hình Bot từ Redis. | - Tự động thiết lập Fallback đọc thẳng từ MySQL.<br>- Ghi log lỗi Redis Cảnh báo mức độ High để chẩn đoán. | [ ] |

---

## 3. Kiểm Thử Bảo Mật (Security Testing)

| TC ID | Kịch Bản & Yêu Cầu Kỹ Thuật | Các Bước Thực Hiện (Mô phỏng bằng Công cụ) | Kết Quả Mong Đợi (SLA) | Trạng Thái |
|---|---|---|---|---|
| TC_SEC_01 | **Thử bypass xác thực API Key**<br>Bảo vệ API khỏi hệ thống bên ngoài thứ 3 truy cập trái phép. | Dùng Postman gửi request tạo đơn hàng (nguồn Bot gọi) mà không gắn Header Key hoặc gắn sai Key. | - Server chặn tại Middleware.<br>- Trả về chuẩn mã HTTP 401 Unauthorized. | [ ] |
| TC_SEC_02 | **Kiểm tra phòng chống SQL Injection**<br>Hack lấy cắp cơ sở dữ liệu. | Tại trường nhập "Mã bưu kiện", điền vào chuỗi truy vấn lấy cắp dữ liệu: `' OR 1=1; DROP TABLE users;--` | - Đầu vào bị Sanitize thành chuỗi ký tự thường.<br>- Hệ thống không thực thi SQL lỗi, báo sai định dạng mã. | [ ] |
| TC_SEC_03 | **Thử giả mạo wxapp_id (ID Tenant)**<br>Hack xem/đánh cắp dữ liệu của chủ shop khác. | Sử dụng Auth Token hợp lệ của user thuộc `wxapp_id = 1`, nhưng gửi tham số ngầm ở endpoint là `wxapp_id = 2` để yêu cầu xem danh sách đơn hàng. | - Hệ thống so sánh đối chiếu Token cấp với dữ liệu truyền tải.<br>- Trả về lỗi bảo mật HTTP 403 Forbidden. | [ ] |
| TC_SEC_04 | **Kiểm tra giới hạn tốc độ (Rate Limit)**<br>Phòng chống bị DDOS làm treo máy chủ. | Dùng K6.io hoặc Jmeter cấu hình vòng lặp bắn liên tục 110 requests tới cùng 1 API trong 60 giây. | - Hệ thống theo dõi Rate Limit ghi nhận đủ 100 requests.<br>- Từ request thứ 101, trả lỗi HTTP 429 Too Many Requests. | [ ] |

---

## 4. Biên Bản Bàn Giao (Deliverables Template)

*Bảng mẫu báo cáo cho Dev / Quản trị hệ thống điền khi chạy lệnh kiểm thử (Benchmark).*

### 4.1. Báo Cáo Hiệu Năng (Performance Report)
- **Công cụ giả lập tải:** ...................................... (VD: Apache Jmeter 5.6)
- **Môi trường:** ................................................. (VD: Staging Server, CPU 4 Core, 8GB RAM)
- **Người chạy kịch bản Benchmark:** ......................................
- **Kết Quả Đo (Benchmark Data):**
  - Số lượng yêu cầu song song tối đa đã thử: ......... req/s (RPS)
  - Số lượng Bot message đưa vào hàng đợi: ......... messages
  - Tổng số lỗi 50x hệ thống sinh ra khi tải cao: ......... lỗi
  - Tỷ lệ Cache Hit trên Redis giám sát: ......... %
- **Kết luận (Đạt / Cần tối ưu thêm code/index DB):** ......................................

### 4.2. Báo Cáo Bảo Mật (Security Report)
- **Danh sách các lỗ hổng tìm thấy:**
  - [ ] Có thể bị gọi API Tenant giả mạo (Chi tiết: ......)
  - [ ] Middleware bị tắt dẫn đến gọi không qua API Key (Chi tiết: ......)
  - [ ] Chưa cấu hình giới hạn 100/req cho IP hoặc User (Chi tiết: ......)
- **Tiêu chí SQL Injection:** [ Đạt ✅ ] Đầu vào đã được bộ thư viện Framework làm sạch hoàn toàn.
- **Tiêu chí JWT Token / Auth:** [ Đạt ✅ ] Đã chặn toàn bộ các truy cập nặc danh.

*(Tài liệu này được tham chiếu trực tiếp từ technical-spec.md - Mục 7 và Mục 9)*
