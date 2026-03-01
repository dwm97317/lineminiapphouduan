# Requirements Document: Financial Data Import

## Introduction

This feature enables one-time migration of historical order payment status from Excel files into the statement billing system. The Excel files contain order data with color-coded rows indicating different payment and billing states. The system will parse these files, detect cell background colors, match orders, and update their status accordingly.

This is a migration tool designed for transitioning from the old system to the new statement billing system, ensuring historical data accuracy without manual processing.

## Glossary

- **Excel_Parser**: Component that reads Excel files and extracts cell data and formatting
- **Color_Detector**: Component that identifies cell background colors using RGB values
- **Order_Matcher**: Component that matches Excel rows to database orders
- **Status_Updater**: Component that updates order payment and billing status
- **History_Statement**: Special statement record created for imported historical data
- **International_Tracking_Number**: The express_num field used for precise order matching
- **Member_ID**: User identifier extracted from Excel cells (numeric portion)
- **Weight_Tolerance**: Acceptable weight difference (±0.5 KG) for fuzzy matching
- **Blue_Row**: Row with blue background indicating paid + billed status
- **Pink_Row**: Row with pink background indicating billed but unpaid status
- **Green_Row**: Row with green background indicating paid but not billed status
- **White_Row**: Row with no background color indicating unprocessed status
- **Preview_Interface**: UI showing import results before confirmation
- **Import_Report**: Summary of successful and failed imports

## Requirements

### Requirement 1: Excel File Parsing

**User Story:** As a finance user, I want to upload multi-sheet Excel files, so that the system can extract order data from all sheets.

#### Acceptance Criteria

1. WHEN a valid Excel file (.xls or .xlsx) is uploaded, THE Excel_Parser SHALL read all sheets in the file
2. FOR each sheet, THE Excel_Parser SHALL extract data from columns A (Member_ID + country), B (weight), C (International_Tracking_Number), and D (date)
3. THE Excel_Parser SHALL skip rows where Member_ID or weight is empty
4. WHEN Member_ID contains country suffix (e.g., "23048泰国"), THE Excel_Parser SHALL extract only the numeric portion
5. WHEN Member_ID cell contains images, THE Excel_Parser SHALL strip non-text content and extract the numeric ID
6. THE Excel_Parser SHALL preserve the sheet name and row number for each extracted record

### Requirement 2: Cell Background Color Detection

**User Story:** As a finance user, I want the system to automatically detect row colors, so that I don't need to manually categorize orders.

#### Acceptance Criteria

1. FOR each data row, THE Color_Detector SHALL examine the background color of columns A and B
2. WHEN the RGB blue value exceeds 200 AND exceeds red by 30 AND exceeds green by 30, THE Color_Detector SHALL classify the row as Blue_Row
3. WHEN the RGB red value exceeds 200 AND green exceeds 150 AND blue exceeds 150, THE Color_Detector SHALL classify the row as Pink_Row
4. WHEN the RGB green value exceeds 200 AND exceeds red by 30 AND exceeds blue by 30, THE Color_Detector SHALL classify the row as Green_Row
5. WHEN the cell has no fill color OR RGB is FFFFFF, THE Color_Detector SHALL classify the row as White_Row
6. WHEN the cell has color but does not match any pattern, THE Color_Detector SHALL classify as "unknown" and display RGB values in Preview_Interface
7. THE Color_Detector SHALL return color classification with confidence level (high, medium, low)

### Requirement 3: Order Matching by International Tracking Number

**User Story:** As a finance user, I want orders matched by tracking number first, so that matching is accurate when tracking numbers are available.

#### Acceptance Criteria

1. WHEN International_Tracking_Number is not empty, THE Order_Matcher SHALL query the database using express_num field
2. THE Order_Matcher SHALL filter by wxapp_id and is_delete = 0
3. WHEN exactly one order is found, THE Order_Matcher SHALL return the order with match_type "exact" and confidence "high"
4. WHEN no order is found by International_Tracking_Number, THE Order_Matcher SHALL proceed to fuzzy matching
5. THE Order_Matcher SHALL record the matching method used for each order

### Requirement 4: Order Matching by Member ID and Weight

**User Story:** As a finance user, I want orders matched by user ID and weight when tracking number is missing, so that all orders can be processed.

#### Acceptance Criteria

1. WHEN International_Tracking_Number is empty OR exact matching fails, THE Order_Matcher SHALL query by Member_ID
2. THE Order_Matcher SHALL apply Weight_Tolerance of ±0.5 KG to the weight condition
3. WHEN date is provided in column D, THE Order_Matcher SHALL restrict the query to that date range
4. WHEN date is not provided, THE Order_Matcher SHALL use the current month as date range
5. WHEN multiple candidate orders are found, THE Order_Matcher SHALL calculate weight difference for each candidate
6. THE Order_Matcher SHALL sort candidates by weight difference ascending
7. WHEN the minimum weight difference is unique, THE Order_Matcher SHALL return that order with match_type "fuzzy" and confidence "medium"
8. WHEN multiple orders have the same minimum weight difference, THE Order_Matcher SHALL return all candidates with match_type "multiple" and confidence "low"
9. WHEN no orders match the criteria, THE Order_Matcher SHALL return null

### Requirement 5: Import Preview Interface

**User Story:** As a finance user, I want to preview import results before confirming, so that I can verify the data is correct.

#### Acceptance Criteria

1. AFTER parsing completes, THE Preview_Interface SHALL display sheet-level statistics showing total rows and color counts per sheet
2. THE Preview_Interface SHALL display summary counts for Blue_Row, Pink_Row, Green_Row, and unmatched orders
3. FOR orders with "unknown" color, THE Preview_Interface SHALL display RGB values and provide a dropdown to manually select color
4. FOR orders with match_type "multiple", THE Preview_Interface SHALL display all candidate orders with radio buttons for manual selection
5. THE Preview_Interface SHALL display a list of unmatched orders with Member_ID, weight, and International_Tracking_Number
6. THE Preview_Interface SHALL group preview data by color category (blue, pink, green, unmatched)
7. THE Preview_Interface SHALL provide "Confirm Import" and "Cancel" buttons
8. WHEN "Cancel" is clicked, THE Preview_Interface SHALL delete temporary files and clear session data

### Requirement 6: Historical Statement Creation

**User Story:** As a finance user, I want historical statements created for imported orders, so that billing records are properly maintained.

#### Acceptance Criteria

1. BEFORE updating order status, THE Status_Updater SHALL group Blue_Row and Pink_Row orders by Member_ID
2. FOR each unique Member_ID requiring a statement, THE Status_Updater SHALL create one History_Statement
3. THE History_Statement SHALL have statement_no format "HISTORY_{Member_ID}_{timestamp}"
4. THE History_Statement SHALL have status set to "confirmed" and pay_status set to "paid"
5. THE History_Statement SHALL have remark field set to "历史数据导入"
6. THE Status_Updater SHALL store the mapping between Member_ID and History_Statement ID

### Requirement 7: Order Status Update for Blue Rows

**User Story:** As a finance user, I want blue-marked orders set to paid with statement, so that they reflect paid and billed status.

#### Acceptance Criteria

1. FOR each Blue_Row with successfully matched order, THE Status_Updater SHALL update is_pay to 1
2. THE Status_Updater SHALL set pay_time to current timestamp
3. THE Status_Updater SHALL set statement_id to the corresponding History_Statement ID for that Member_ID
4. THE Status_Updater SHALL only update orders where is_delete = 0

### Requirement 8: Order Status Update for Pink Rows

**User Story:** As a finance user, I want pink-marked orders linked to statement but kept unpaid, so that they reflect billed but unpaid status.

#### Acceptance Criteria

1. FOR each Pink_Row with successfully matched order, THE Status_Updater SHALL set statement_id to the corresponding History_Statement ID
2. THE Status_Updater SHALL NOT modify the is_pay field
3. THE Status_Updater SHALL NOT modify the pay_time field
4. THE Status_Updater SHALL only update orders where is_delete = 0

### Requirement 9: Order Status Update for Green Rows

**User Story:** As a finance user, I want green-marked orders marked as paid without statement, so that they reflect paid but not billed status.

#### Acceptance Criteria

1. FOR each Green_Row with successfully matched order, THE Status_Updater SHALL update is_pay to 1
2. THE Status_Updater SHALL set pay_time to current timestamp
3. THE Status_Updater SHALL NOT modify the statement_id field
4. THE Status_Updater SHALL only update orders where is_delete = 0

### Requirement 10: Transaction Management per Customer

**User Story:** As a finance user, I want updates processed per customer in separate transactions, so that one customer's failure doesn't affect others.

#### Acceptance Criteria

1. THE Status_Updater SHALL group all orders (blue, pink, green) by Member_ID
2. FOR each Member_ID group, THE Status_Updater SHALL execute updates within a single database transaction
3. WHEN a transaction fails for one Member_ID, THE Status_Updater SHALL rollback only that Member_ID's updates
4. THE Status_Updater SHALL continue processing remaining Member_ID groups after a failure
5. THE Status_Updater SHALL record the error message for each failed Member_ID group

### Requirement 11: Import Report Generation

**User Story:** As a finance user, I want a detailed import report, so that I can verify what was imported and troubleshoot failures.

#### Acceptance Criteria

1. AFTER import completes, THE Status_Updater SHALL generate an Import_Report
2. THE Import_Report SHALL include total counts by color (blue, pink, green)
3. THE Import_Report SHALL include success count and failure count
4. FOR each failed Member_ID group, THE Import_Report SHALL include Member_ID and error message
5. THE Import_Report SHALL include the list of unmatched orders with their Excel data
6. THE Import_Report SHALL display in the UI after import completion
7. WHERE export functionality is implemented, THE Import_Report SHALL be exportable as Excel file

### Requirement 12: File Upload Validation

**User Story:** As a finance user, I want invalid files rejected immediately, so that I don't waste time on incorrect uploads.

#### Acceptance Criteria

1. WHEN a file is uploaded, THE Excel_Parser SHALL validate the file extension
2. IF the file extension is not .xls or .xlsx, THEN THE Excel_Parser SHALL return an error message
3. WHEN the file cannot be parsed by PhpSpreadsheet, THE Excel_Parser SHALL return a descriptive error message
4. THE Excel_Parser SHALL store uploaded files in a temporary directory
5. WHEN import is cancelled or completed, THE Excel_Parser SHALL delete the temporary file

### Requirement 13: Manual Correction Interface

**User Story:** As a finance user, I want to manually correct ambiguous matches, so that all orders can be imported accurately.

#### Acceptance Criteria

1. WHERE Color_Detector returns "unknown" color, THE Preview_Interface SHALL display a dropdown with options: blue, pink, green, skip
2. WHERE Order_Matcher returns multiple candidates, THE Preview_Interface SHALL display all candidates with order details
3. THE Preview_Interface SHALL provide radio buttons to select the correct order from candidates
4. WHEN user selects a color or order, THE Preview_Interface SHALL update the preview data accordingly
5. THE Preview_Interface SHALL validate that all "unknown" colors and "multiple" matches are resolved before allowing confirmation

### Requirement 14: White Row Handling

**User Story:** As a finance user, I want white rows ignored during import, so that only marked orders are processed.

#### Acceptance Criteria

1. WHEN Color_Detector classifies a row as White_Row, THE Excel_Parser SHALL skip that row
2. White_Row data SHALL NOT appear in the Preview_Interface
3. White_Row data SHALL NOT be included in any statistics or reports
4. THE Excel_Parser SHALL continue processing subsequent rows after encountering White_Row

### Requirement 15: Date Range Parsing

**User Story:** As a finance user, I want dates in various formats recognized, so that date-based matching works correctly.

#### Acceptance Criteria

1. WHEN column D contains Chinese date format (e.g., "2月13日"), THE Order_Matcher SHALL parse it to month and day
2. THE Order_Matcher SHALL use the current year when year is not specified
3. THE Order_Matcher SHALL construct date range from start of day (00:00:00) to end of day (23:59:59)
4. WHEN column D is empty, THE Order_Matcher SHALL use the first day to last day of current month as date range
5. WHEN date parsing fails, THE Order_Matcher SHALL log a warning and use current month as fallback
