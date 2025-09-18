# TODO List for Implementing Roles, Product Image Upload, and Login Logging

- [x] Create migration: add_role_to_users_table.php (add role enum column)
- [x] Update app/Models/User.php (add 'role' to fillable)
- [x] Create migration: add_image_to_products_table.php (add image string column)
- [x] Update app/Models/Product.php (add 'image' to fillable)
- [x] Update app/Http/Requests/ProductRequest.php (add image validation rules)
- [x] Update app/Http/Controllers/API/ProductController.php (handle image upload in store and update methods)
- [x] Create app/Models/LoginLog.php model
- [x] Create migration: create_login_logs_table.php (user_id, login_at)
- [x] Update app/Http/Controllers/API/AuthController.php (log login in login method)
- [x] Run php artisan migrate
- [x] Test functionality
