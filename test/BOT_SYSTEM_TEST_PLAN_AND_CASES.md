# Kế Hoạch & Kịch Bản Kiểm Thử Hệ Thống Bot

Tài liệu này cung cấp toàn bộ kịch bản kiểm thử (Test Cases) cho hệ thống Bot dựa trên phạm vi yêu cầu, bao gồm các phần: liên kết tài khoản, phiên đặt hàng, mã bưu kiện, chống nhầm đơn, cách ly đa tenant và các tình huống ngoại lệ.

## 1. Kiểm Thử Chức Năng (Functional Testing)

### 1.1. Luồng liên kết tài khoản
| TC ID | Tên Kịch Bản (Test Case) | Các Bước Thực Hiện (Steps) | Kết Quả Mong Đợi (Expected Result) | Trạng Thái |
|---|---|---|---|---|
| TC_FUNC_01 | Liên kết Customer ID hợp lệ | 1. Mở Bot.<br>2. Chọn tính năng Liên kết tài khoản.<br>3. Nhập Customer ID hợp lệ. | Bot thông báo "Liên kết thành công", hiển thị tên khách hàng và mở khóa các tính năng đặt hàng. | [ ] |
| TC_FUNC_02 | Liên kết Customer ID không hợp lệ | 1. Mở Bot.<br>2. Chọn tính năng Liên kết tài khoản.<br>3. Nhập Customer ID sai/không tồn tại. | Bot thông báo lỗi "ID không hợp lệ hoặc không tồn tại". Yêu cầu nhập lại. Bot không cho phép đặt hàng khi chưa liên kết. | [ ] |

### 1.2. Tạo phiên đặt hàng mới
| TC ID | Tên Kịch Bản (Test Case) | Các Bước Thực Hiện (Steps) | Kết Quả Mong Đợi (Expected Result) | Trạng Thái |
|---|---|---|---|---|
| TC_FUNC_03 | Tạo phiên từ Facebook | 1. Gửi tin nhắn đặt hàng qua Facebook Messenger.<br>2. Bắt đầu luồng tạo đơn. | Hệ thống khởi tạo một phiên đặt hàng mới (Order Session), ghi nhận nguồn là 'FB'. | [ ] |
| TC_FUNC_04 | Tạo phiên từ Instagram | 1. Gửi tin nhắn đặt hàng qua Instagram Direct.<br>2. Bắt đầu luồng tạo đơn. | Hệ thống khởi tạo một phiên đặt hàng mới, ghi nhận nguồn là 'IG'. | [ ] |

### 1.3. Luồng bổ sung thông tin
| TC ID | Tên Kịch Bản (Test Case) | Các Bước Thực Hiện (Steps) | Kết Quả Mong Đợi (Expected Result) | Trạng Thái |
|---|---|---|---|---|
| TC_FUNC_05 | Bổ sung đầy đủ thông tin đơn hàng | 1. Đang trong phiên đơn hàng chờ.<br>2. Gửi các thông tin: Tên shop, ngày, số tiền, mã bưu kiện. | Bot bóc tách đúng các thông tin, cập nhật vào phiên đặt hàng hiện tại và phản hồi xác nhận. | [ ] |

### 1.4. Liên kết mã bưu kiện
| TC ID | Tên Kịch Bản (Test Case) | Các Bước Thực Hiện (Steps) | Kết Quả Mong Đợi (Expected Result) | Trạng Thái |
|---|---|---|---|---|
| TC_FUNC_06 | Nhập mã bưu kiện bình thường | 1. Gửi 1 mã bưu kiện hợp lệ chưa từng được tạo. | Bot thông báo "Thêm mã bưu kiện thành công". | [ ] |
| TC_FUNC_07 | Nhập mã bưu kiện trùng lặp | 1. Gửi mã bưu kiện A (đã được liên kết trên hệ thống). | Bot báo lỗi: "Mã bưu kiện đã tồn tại" và từ chối thêm mã vào đơn mới. | [ ] |
| TC_FUNC_08 | Nhập sai định dạng mã | 1. Gửi mã chứa ký tự đặc biệt hoặc quá ngắn/quá dài. | Bot báo lỗi: "Sai định dạng mã bưu kiện". | [ ] |

### 1.5. Kích hoạt đơn & Nhiều đơn cùng lúc
| TC ID | Tên Kịch Bản (Test Case) | Các Bước Thực Hiện (Steps) | Kết Quả Mong Đợi (Expected Result) | Trạng Thái |
|---|---|---|---|---|
| TC_FUNC_09 | Kích hoạt phiên bằng từ khóa | 1. Gửi từ khóa như "đặt hàng", "mua", "tạo đơn". | Bot tự động nhận diện từ khóa và kích hoạt một phiên tạo đơn chờ. | [ ] |
| TC_FUNC_10 | Thao tác 2 đơn cùng lúc | 1. Tạo đơn 1 (chưa hoàn thành).<br>2. Kích hoạt tạo đơn 2. | Hệ thống treo đơn 1 và báo: "Bạn có đơn chưa tạo xong. Bỏ qua để tạo đơn mới hay tiếp tục đơn cũ?". | [ ] |

---

## 2. Kiểm Thử Chống Nhầm Đơn (Anti-collision Testing)

| TC ID | Tên Kịch Bản (Test Case) | Các Bước Thực Hiện (Steps) | Kết Quả Mong Đợi (Expected Result) | Trạng Thái |
|---|---|---|---|---|
| TC_ANTI_01 | Timeout 24h tự động tạo phiên mới | 1. Có một phiên đơn hàng mở quá 24h.<br>2. Khách nhắn tin thêm thông tin. | Hệ thống hủy/đóng phiên cũ, tự động khởi tạo phiên đặt hàng mới hoàn toàn. | [ ] |
| TC_ANTI_02 | Cập nhật cùng người bán | 1. Đang có đơn dở với Shop A.<br>2. Khách vẫn nhắn tin với Shop A. | Thông tin gửi lên tự động được gộp (append) vào phiên đang mở của Shop A mà không tạo đơn mới. | [ ] |
| TC_ANTI_03 | Người bán khác hiện nút xác nhận | 1. Đang có đơn dở với Shop A.<br>2. Khách nhắn tạo đơn với Shop B. | Bot nhận diện khác người bán và hiển thị Nút xác nhận: "Xác nhận tạo đơn mới cho Shop B?". | [ ] |
| TC_ANTI_04 | Khách chủ động chọn「Đơn mới」 | 1. Khách nhấn chọn nút「Đơn mới」. | Bot đóng/tạm ngưng phiên của Shop A, khởi tạo riêng phiên cho Shop B. | [ ] |

---

## 3. Kiểm Thử Cách Ly Đa Tenant (Multi-tenant Isolation)

| TC ID | Tên Kịch Bản (Test Case) | Các Bước Thực Hiện (Steps) | Kết Quả Mong Đợi (Expected Result) | Trạng Thái |
|---|---|---|---|---|
| TC_TENANT_01 | Cách ly dữ liệu theo wxapp_id | 1. Dùng wxapp_id = 1 tạo đơn KH_A.<br>2. Đăng nhập hệ thống bằng wxapp_id = 2 và tìm KH_A. | wxapp_id = 2 không thể tìm thấy, không hiển thị dữ liệu của wxapp_id = 1. | [ ] |
| TC_TENANT_02 | Truy cập chéo tenant | 1. Gọi API lấy dữ liệu của wxapp_id = 1 bằng JWT Token/Quyền của wxapp_id = 2. | API ngay lập tức trả về lỗi HTTP 403 Forbidden hoặc 401 Unauthorized. | [ ] |

---

## 4. Kiểm Thử Tình Huống Ngoại Lệ (Exception Handling)

| TC ID | Tên Kịch Bản (Test Case) | Các Bước Thực Hiện (Steps) | Kết Quả Mong Đợi (Expected Result) | Trạng Thái |
|---|---|---|---|---|
| TC_EXC_01 | Timeout mạng và tính năng Retry | 1. Gửi yêu cầu API và ngắt mạng (mô phỏng timeout mạng). | Hệ thống Bot phải có cơ chế retry tự động (vd: 3 lần), nếu thất bại thì báo cho user "Mạng chậm, vui lòng thử lại sau". Không bị crash app/bot. | [ ] |
| TC_EXC_02 | Phá vỡ giới hạn truy cập (Chưa liên kết tài khoản) | 1. Khách chưa liên kết tài khoản cố tình gửi lệnh/API mua hàng. | Hệ thống chặn yêu cầu tại Middleware, bot phản hồi "Quý khách cần liên kết tài khoản để sử dụng tính năng này". | [ ] |

---

## 5. Mẫu Báo Cáo Kiểm Thử (Test Report Template)

*Bản báo cáo này sẽ được kỹ sư QA (Tester) hoặc quản lý dự án điền sau khi quá trình test kết thúc.*

- **Người thực hiện:** ...........................................
- **Ngày thực hiện:** ...../...../202...
- **Môi trường:** (VD: Staging, Production)

**Tổng quan kết quả:**
- Tổng số Test Case (TC): 16
- Số TC Đạt (Passed): 0
- Số TC Lỗi (Failed): 0
- Số TC Bỏ qua (Skipped): 0
- Tỷ lệ Pass: 0%

---

## 6. Mẫu Danh Sách Bug (Bug List - Tạo Issue)

*Nếu một Test case ở trên bị FAIL, sử dụng bảng này để log lỗi gửi lập trình viên.*

| Bug ID | Mức độ nghiêm trọng (Severity) | Môi trường test | Tiêu đề lỗi (Bug Summary) | Các bước tái hiện (Steps) | Kết quả thực tế (Actual Result) | Trạng thái (Tricket Status) |
|---|---|---|---|---|---|---|
| BUG-001 | High (Cao) | Staging - FB Bot | Lỗi không báo trùng lặp mã bưu kiện | Gửi mã bưu kiện "112233" 2 lần liên tiếp | Không hiện thông báo lỗi, backend vẫn tạo 2 bản ghi trùng trong DB | Open (Đang chờ dev sửa) |
| BUG-002 | Critical (Nghiêm trọng) | Staging - Bot App | Có thể gọi API lấy dữ liệu của Tenant khác | Đổi parameter `wxapp_id = 2` bằng token của `wxapp_id = 1` | API vẫn trả về thông tin khách hàng của người kia | Open |
