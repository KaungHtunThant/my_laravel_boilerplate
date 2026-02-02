# Laravel Controller-Service-Repository Boilerplate

A Laravel boilerplate implementing the Controller-Service-Repository pattern for clean, maintainable, and testable code architecture.

## Architecture Pattern

This boilerplate implements a three-layer architecture:

### 1. Controller Layer
- **Responsibility**: Handle HTTP requests and responses only
- **Rules**:
  - Can use only ONE service
  - Handles request validation
  - Formats responses
  - Does NOT contain business logic

### 2. Service Layer
- **Responsibility**: Business logic and application workflows
- **Rules**:
  - Can use MULTIPLE repositories
  - Can use other services
  - Contains all business logic
  - Orchestrates data operations

### 3. Repository Layer
- **Responsibility**: Data access and model CRUD operations
- **Rules**:
  - Direct interaction with Eloquent models
  - Handles all database operations
  - Returns model instances or collections
  - No business logic

## Features

- ✅ Custom Artisan commands for generating Services and Repositories
- ✅ Example implementation with User model
- ✅ RESTful API endpoints
- ✅ Comprehensive test suite
- ✅ Clean separation of concerns

## Installation

1. Clone the repository
```bash
git clone https://github.com/KaungHtunThant/my_laravel_boilerplate.git
cd my_laravel_boilerplate
```

2. Install dependencies
```bash
composer install
```

3. Set up environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations
```bash
php artisan migrate
```

## Custom Artisan Commands

### Create a Service

```bash
php artisan make:service YourService
```

This creates a new service class in `app/Services/YourService.php`

### Create a Repository

```bash
php artisan make:repository YourRepository
```

This creates a new repository class in `app/Repositories/YourRepository.php`

## Example Implementation: User Module

### Directory Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           └── UserController.php    # Handles requests/responses
├── Services/
│   └── UserService.php               # Business logic
└── Repositories/
    └── UserRepository.php            # Database operations
```

### API Endpoints

All User endpoints are available under `/api/v1/users`:

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/users` | List all users (paginated) |
| POST | `/api/v1/users` | Create a new user |
| GET | `/api/v1/users/{id}` | Get a specific user |
| PUT/PATCH | `/api/v1/users/{id}` | Update a user |
| DELETE | `/api/v1/users/{id}` | Delete a user |

### Example API Usage

#### Create a User
```bash
curl -X POST http://localhost:8000/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Get All Users
```bash
curl http://localhost:8000/api/v1/users
```

#### Get Specific User
```bash
curl http://localhost:8000/api/v1/users/1
```

#### Update User
```bash
curl -X PUT http://localhost:8000/api/v1/users/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe"
  }'
```

#### Delete User
```bash
curl -X DELETE http://localhost:8000/api/v1/users/1
```

## Testing

Run all tests:
```bash
php artisan test
```

Run specific test suite:
```bash
php artisan test --filter=UserControllerTest
```

The boilerplate includes comprehensive tests for:
- User listing and pagination
- User creation with validation
- User retrieval
- User updates
- User deletion
- Error handling (404, validation errors)

## Code Examples

### Controller Example (UserController.php)

```php
public function __construct(protected UserService $userService)
{
}

public function store(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email|max:255',
        'password' => 'required|string|min:8',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    $user = $this->userService->createUser($request->only(['name', 'email', 'password']));

    return response()->json([
        'success' => true,
        'message' => 'User created successfully',
        'data' => $user,
    ], 201);
}
```

### Service Example (UserService.php)

```php
public function __construct(protected UserRepository $userRepository)
{
}

public function createUser(array $data): array
{
    // Business logic: Hash password
    if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
    }

    $user = $this->userRepository->create($data);

    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'created_at' => $user->created_at,
    ];
}
```

### Repository Example (UserRepository.php)

```php
public function __construct(protected User $model)
{
}

public function create(array $data): User
{
    return $this->model->create($data);
}

public function findById(int $id): ?User
{
    return $this->model->find($id);
}
```

## Best Practices

1. **Controllers**: Keep them thin, only handle HTTP concerns
2. **Services**: Place all business logic here, make it testable
3. **Repositories**: Keep database queries isolated and reusable
4. **Dependency Injection**: Use constructor injection for dependencies
5. **Type Hints**: Always use type hints for better code quality
6. **Testing**: Write tests for controllers, services can be tested through controllers

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
