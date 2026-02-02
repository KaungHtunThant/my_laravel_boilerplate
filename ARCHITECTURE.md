# Architecture Diagram

## Controller-Service-Repository Pattern Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                         HTTP Request                                 │
│                              ↓                                       │
├─────────────────────────────────────────────────────────────────────┤
│                      CONTROLLER LAYER                                │
│                   (UserController.php)                               │
│                                                                       │
│  Responsibilities:                                                   │
│  • Validate HTTP requests                                            │
│  • Call service methods                                              │
│  • Format HTTP responses                                             │
│  • Handle only request/response                                      │
│                              ↓                                       │
├─────────────────────────────────────────────────────────────────────┤
│                       SERVICE LAYER                                  │
│                     (UserService.php)                                │
│                                                                       │
│  Responsibilities:                                                   │
│  • Business logic (e.g., password hashing)                           │
│  • Data transformation                                               │
│  • Orchestrate repository calls                                      │
│  • Can use multiple repositories                                     │
│                              ↓                                       │
├─────────────────────────────────────────────────────────────────────┤
│                     REPOSITORY LAYER                                 │
│                   (UserRepository.php)                               │
│                                                                       │
│  Responsibilities:                                                   │
│  • CRUD operations                                                   │
│  • Direct database queries                                           │
│  • Interact with Eloquent models                                     │
│  • Return model instances                                            │
│                              ↓                                       │
├─────────────────────────────────────────────────────────────────────┤
│                         MODEL LAYER                                  │
│                        (User.php)                                    │
│                                                                       │
│  • Eloquent ORM model                                                │
│  • Database table representation                                     │
│                              ↓                                       │
├─────────────────────────────────────────────────────────────────────┤
│                         DATABASE                                     │
└─────────────────────────────────────────────────────────────────────┘
```

## Example: Creating a User

### Step-by-Step Flow

1. **HTTP Request arrives at Controller**
   ```php
   POST /api/v1/users
   {
     "name": "John Doe",
     "email": "john@example.com",
     "password": "password123"
   }
   ```

2. **Controller validates and calls Service**
   ```php
   // UserController.php
   public function store(Request $request): JsonResponse
   {
       // Validate input
       $validator = Validator::make($request->all(), [...]);
       
       // Call service
       $user = $this->userService->createUser($request->only([...]));
       
       // Return formatted response
       return response()->json([...], 201);
   }
   ```

3. **Service applies business logic and calls Repository**
   ```php
   // UserService.php
   public function createUser(array $data): array
   {
       // Business logic: Hash password
       $data['password'] = Hash::make($data['password']);
       
       // Call repository
       $user = $this->userRepository->create($data);
       
       // Format and return data
       return [...];
   }
   ```

4. **Repository interacts with Model/Database**
   ```php
   // UserRepository.php
   public function create(array $data): User
   {
       // Direct database operation
       return $this->model->create($data);
   }
   ```

5. **Response flows back up the chain**
   ```
   Database → Model → Repository → Service → Controller → HTTP Response
   ```

## Dependency Injection Flow

```
┌───────────────────┐
│  UserController   │
│                   │
│  Needs:           │
│  • UserService    │◄──────┐
└───────────────────┘        │
                             │ Injected by
┌───────────────────┐        │ Laravel's
│   UserService     │        │ Container
│                   │        │
│  Needs:           │        │
│  • UserRepository │◄───────┤
└───────────────────┘        │
                             │
┌───────────────────┐        │
│  UserRepository   │        │
│                   │        │
│  Needs:           │        │
│  • User Model     │◄───────┘
└───────────────────┘
```

## Benefits

### 1. Separation of Concerns
- Each layer has a single, well-defined responsibility
- Changes in one layer don't affect others

### 2. Testability
```
Unit Tests:
├── Repository Tests (test database operations)
├── Service Tests (test business logic)
└── Controller Tests (test HTTP handling) ✅ Implemented
```

### 3. Reusability
- Repositories can be reused across services
- Services can be reused across controllers

### 4. Maintainability
- Easy to locate and modify specific functionality
- Clear structure for team collaboration

## Command Line Tools

### Generate Service
```bash
php artisan make:service ProductService
```

Creates:
```
app/Services/ProductService.php
```

### Generate Repository
```bash
php artisan make:repository ProductRepository
```

Creates:
```
app/Repositories/ProductRepository.php
```

## Testing Strategy

```
┌─────────────────────────────────────────┐
│     Feature Tests (End-to-End)         │
│                                         │
│  • Test full request/response cycle    │
│  • Test via UserController              │
│  • Verify database changes              │
│  • Check HTTP status codes              │
│                                         │
│  ✅ 12 tests implemented                │
└─────────────────────────────────────────┘
           ↓ Tests
┌─────────────────────────────────────────┐
│        Controller Layer                 │
└─────────────────────────────────────────┘
           ↓ Uses
┌─────────────────────────────────────────┐
│         Service Layer                   │
└─────────────────────────────────────────┘
           ↓ Uses
┌─────────────────────────────────────────┐
│       Repository Layer                  │
└─────────────────────────────────────────┘
```

---

**This architecture ensures scalable, maintainable, and testable code.**
