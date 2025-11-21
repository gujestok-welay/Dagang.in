# 🧪 Testing Checklist - Priority 3 Features

## Database Migration

- [ ] Run migration via `/admin/run_migration.php`
- [ ] Verify `categories` table created
- [ ] Verify `category_id` column added to `products`
- [ ] Verify foreign key relationships established

## Product Categories

- [ ] Access `/admin/categories.php`
- [ ] Create new category with name, description, color
- [ ] Edit existing category
- [ ] Toggle category active/inactive status
- [ ] Delete category
- [ ] Verify categories appear in product add/edit dropdowns

## Enhanced Dashboard

- [ ] Access `/admin/dashboard.php`
- [ ] Verify all statistics cards display correctly:
  - [ ] Total Products count
  - [ ] Products in Stock count
  - [ ] Low Stock Products alert
  - [ ] Total Orders count
  - [ ] Pending Orders count
  - [ ] Total Revenue (with Rp format)
  - [ ] Monthly Revenue
  - [ ] Unique Customers count
- [ ] Verify Chart.js doughnut chart renders with order status data
- [ ] Verify Best Sellers list displays
- [ ] Verify Quick Access buttons are functional
- [ ] Click through quick access buttons

## Bulk Actions - Products

- [ ] Go to `/admin/products.php`
- [ ] Select single product checkbox
- [ ] Verify bulk actions bar appears with selected count
- [ ] Select multiple products
- [ ] Verify selected count updates correctly
- [ ] Click "Delete" button
- [ ] Confirm deletion in dialog
- [ ] Verify products deleted from list
- [ ] Verify "Cancel" button clears all selections
- [ ] Verify checkboxes uncheck after bulk delete

## Bulk Actions - Orders

- [ ] Go to `/admin/orders.php`
- [ ] (Note: Current implementation shows bulk actions UI, may need further testing)

## Export Features - Products

- [ ] Go to `/admin/products.php`
- [ ] Click "Export" dropdown
- [ ] Click "Export ke CSV"
- [ ] Verify CSV file downloads with:
  - [ ] Correct filename format (produk_YYYY-MM-DD_HH-MM-SS.csv)
  - [ ] All products included
  - [ ] Correct columns: ID, Nama, Kategori, Deskripsi, Harga, Stok, Dibuat, Diupdate
  - [ ] UTF-8 encoding (Indonesian characters display correctly)
- [ ] Click "Export ke Excel"
- [ ] Verify Excel file downloads with:
  - [ ] Correct filename format
  - [ ] All products included
  - [ ] Correct columns with green header
  - [ ] UTF-8 encoding

## Export Features - Orders

- [ ] Go to `/admin/orders.php`
- [ ] Click "Export" dropdown
- [ ] Click "Export ke CSV"
- [ ] Verify CSV file downloads with:
  - [ ] Correct filename format (order_YYYY-MM-DD_HH-MM-SS.csv)
  - [ ] All orders included
  - [ ] Correct columns: ID, Nama Pelanggan, Email, Telepon, Total Item, Total Qty, Total Harga, Status, Dibuat, Diupdate
  - [ ] Order IDs prefixed with "ORDER-"
  - [ ] Status labels in Indonesian (Menunggu, Diproses, etc.)
  - [ ] Currency formatted correctly (Rp format)
- [ ] Click "Export ke Excel"
- [ ] Verify Excel file downloads with same data and formatting

## Search & Filter (from Priority 2)

- [ ] Products page:

  - [ ] Search by product name
  - [ ] Filter by price range
  - [ ] Filter by stock status (In Stock, Low Stock, Out of Stock)
  - [ ] Filter by category
  - [ ] Verify pagination with filters
  - [ ] Verify "Reset Filters" button clears all

- [ ] Orders page:
  - [ ] Search by customer name/phone
  - [ ] Filter by status
  - [ ] Verify pagination with filters

## Secure Image Upload

- [ ] Add Product:

  - [ ] Upload valid image (JPG, PNG, GIF)
  - [ ] Verify image saved securely
  - [ ] Try upload invalid file (should fail)
  - [ ] Try upload oversized image (should fail)
  - [ ] Try upload invalid format (should fail)

- [ ] Edit Product:
  - [ ] Replace image with new one
  - [ ] Verify old image deleted
  - [ ] Keep without changing image
  - [ ] Verify image still exists

## Error Handling

- [ ] Verify error messages display correctly
- [ ] Check console for JavaScript errors
- [ ] Verify form validation errors
- [ ] Test with various invalid inputs

## UI/UX

- [ ] All pages responsive on mobile/tablet/desktop
- [ ] Bootstrap styling consistent
- [ ] Icons display correctly (FontAwesome)
- [ ] Forms align properly
- [ ] Buttons functional

## Performance

- [ ] Check database query execution time
- [ ] Verify pagination improves page load
- [ ] Check export functionality performance with large datasets

## Indonesian Language

- [ ] Verify all UI labels in Indonesian
- [ ] Check all form labels in Indonesian
- [ ] Verify all messages/alerts in Indonesian
- [ ] Check export data formatting (dates, currency)

---

## 📝 Notes

- All testing should be done in a browser with developer tools open
- Check browser console for any JavaScript errors
- Monitor database logs for any SQL errors
- Test with multiple user accounts if possible
- Test with various data volumes

## 🚀 Post-Testing Checklist

- [ ] All tests passed
- [ ] No console errors
- [ ] No database errors
- [ ] Documentation updated
- [ ] Ready for deployment
