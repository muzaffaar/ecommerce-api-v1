## API Routes of Ecommerce project version 1 dedicated for only single merchandizing. (under process)

### Authentication

- **POST** `/api/login` - Login to the application
- **POST** `/api/register` - Register a new user
- **POST** `/api/password/email` - Send reset password email
- **POST** `/api/password/reset` - Reset user password

### Categories

- **GET** `/api/categories` - List all categories
- **GET** `/api/categories/{slug}` - Show a specific category

### Products

- **GET** `/api/products` - List all products
- **GET** `/api/products/{slug}` - Show a specific product
- **GET** `/api/products/search` - Search products
- **POST** `/api/products` - Store a new product
- **PUT** `/api/products/{slug}` - Update a product
- **DELETE** `/api/products/{slug}` - Delete a product

### Cart

- **POST** `/api/carts/add-item` - Add an item to the cart
- **DELETE** `/api/carts/delete-item` - Delete an item from the cart
- **PUT** `/api/carts/update-item` - Update an item in the cart
- **GET** `/api/carts/show-all-items` - Show all items in the cart

### Payment

- **POST** `/api/payment` - Process payment
- **GET** `/api/success` - Payment success callback
- **GET** `/api/cancel` - Payment cancel callback

### Admin (Requires Admin Role)

- **POST** `/api/categories` - Create a new category
- **PUT** `/api/categories/{slug}` - Update a category
- **DELETE** `/api/categories/{slug}` - Delete a category
- **POST** `/api/products` - Create a new product
- **PUT** `/api/products/{slug}` - Update a product
- **DELETE** `/api/products/{slug}` - Delete a product

### Verification (Requires Authenticated User)

- **GET** `/api/phone-verification` - Send phone verification code
- **POST** `/api/phone-verification-resend` - Resend phone verification code
- **POST** `/api/phone-verify` - Verify phone verification code
- **GET** `/api/email/verify` - View email verification notice
- **POST** `/api/email/resend` - Resend email verification email
- **GET** `/api/email/verify/{id}/{hash}` - Verify email with signed URL

### Logout

- **POST** `/api/logout` - Logout the authenticated user
