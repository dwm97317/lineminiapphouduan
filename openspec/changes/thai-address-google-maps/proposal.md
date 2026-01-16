# Change: Thai Address Support & Google Maps Integration

## Why
Thailand has a unique address structure (Province, District, Sub-district, Postal Code) that differs from the 3-level "Province-City-District" model used in China. Additionally, manual address entry in Thailand is prone to errors. Integrating Google Maps for location picking and reverse geocoding will significantly improve UX and logistics accuracy for the LINE Mini App.

## What Changes
- **Configuration**:
  - Add `google_maps_key` to `line_config` settings in the admin backend.
- **Database**:
  - Add `latitude`, `longitude`, `postal_code`, and `sub_district` (for Tambon) fields to `yoshop_user_address` and `yoshop_order_address`.
- **Backend API**:
  - Return Google Maps Key in `LineApp/base` for frontend initialization.
  - Update `UserAddress` model to handle new fields.
  - Provide a new endpoint for reverse geocoding (optional, can be done client-side).
- **Admin Backend**:
  - Update LINE configuration view to include the Google Maps API Key field.

## Impact
- **Affected Specs**: 
  - `admin-settings`
  - `line-app`
- **Affected Code**:
  - `source/application/common/model/Setting.php`
  - `source/application/api/model/UserAddress.php`
  - `source/application/store/view/setting/line_config/index.php`
  - `source/application/api/controller/LineApp.php`
