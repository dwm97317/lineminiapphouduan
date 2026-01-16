## 1. Implementation
- [x] 1.1 Update `Setting` model defaults to include `google_maps_key`
- [x] 1.2 Update `LineConfig` admin view to add the Google Maps API Key field
- [x] 1.3 Add database fields (`latitude`, `longitude`, `postal_code`, `sub_district`) to `yoshop_user_address`
- [x] 1.4 Add database fields to `yoshop_order_address`
- [x] 1.5 Update `UserAddress` API model to handle the new fields
- [x] 1.6 Update `LineApp` base controller to return the API Key
- [x] 1.7 Refactor `OrderAddress` and `UserAddress` for Thai formatting
- [x] 1.8 Create `GoogleMaps` service for reverse geocoding
- [x] 1.9 Implement `parseAddress` API in `LineApp` controller
- [x] 1.10 Update `Checkout` service to preserve international address fields

## 2. Validation
- [ ] 2.1 Verify Google Maps Key can be saved in admin backend
- [ ] 2.2 Verify `GET /api/line_app/base` returns the Google Maps Key
- [ ] 2.3 Verify `UserAddress` add/edit methods correctly store new fields
- [ ] 2.4 Verify `OrderAddress` correctly displays formatted Thai addresses
- [ ] 2.5 Test `parseAddress` API with valid coordinates

