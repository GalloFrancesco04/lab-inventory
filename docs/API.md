# API Documentation - Laboratory Inventory System

| | |
|---|---|
| Document Version | 2.0 |
| Base URL | http://localhost:8080 |
| Request Format | application/json |
| Response Format | application/json |
| Encoding | UTF-8 |

**Responses**

- Success: `{ "data": ... }`
- Error: `{ "error": { "code": "...", "message": "..." } }`

**Access**

- Every endpoint except login, logout and status requires a logged-in user (session cookie set by the login). Without it the API answers `401 Unauthorized`.
- Technicians can read every list and detail, but can only close maintenances and add/edit operations, and only for the maintenances assigned to them. Any other change answers `403 Forbidden`.

**Codes used in the data**

- Client state: `1` = active, `0` = blocked
- Object state: `1` = Available, `2` = Loaned, `3` = InMaintenance, `4` = Damaged, `5` = OutOfService
- User role: `1` = Administrator, `2` = Technician
- Dates: `YYYY-MM-DD`

## 1. Authentication

Management of user access (Administrators and Technicians).

### 1.1 User Login

Checks the credentials and starts the session (the browser keeps the session cookie).

- Method: POST
- Endpoint: `/api/login`
- Request Body (JSON):

```json
{
    "email": "admin@lab.it",
    "password": "admin123"
}
```

- Success Response (200 OK): the logged user.

```json
{ "data": { "id": 1, "email": "admin@lab.it", "role": 1 } }
```

- Error Response (401 Unauthorized): Invalid credentials.

### 1.2 Logout

Ends the current user session.

- Method: POST
- Endpoint: `/api/logout`
- Request Body: None.

### 1.3 Check Status

Verifies if the user session is active and returns the logged user (same format as the login), or `401` if there is no session.

- Method: GET
- Endpoint: `/api/auth/status`

## 2. Client Management

Records of individuals authorized to request loans.

### 2.1 List Clients

Retrieves the complete list of all registered clients, each with the ids of its loans.

- Method: GET
- Endpoint: `/api/clients`

### 2.2 Client Details

Retrieves personal data for a single client via their ID.

- Method: GET
- Endpoint: `/api/clients/{id}`
- Example URL: `/api/clients/1`

### 2.3 Create Client

Registers a new client in the database.

- Method: POST
- Endpoint: `/api/clients`
- Request Body (JSON):

```json
{
    "name": "Mario",
    "surname": "Rossi",
    "email": "mario.rossi@email.com",
    "state": 1
}
```

### 2.4 Update Client

Updates the name of an existing client (the email and the state cannot be changed here).

- Method: PUT
- Endpoint: `/api/clients/{id}`
- Request Body (JSON):

```json
{
    "name": "Mario",
    "surname": "Bianchi"
}
```

### 2.5 Block/Unblock Client

Changes the operational status of a client (Blocked/Active). Blocked clients cannot receive new loans.

- Method: POST
- Endpoint: `/api/clients/toggle-block`
- Request Body (JSON):

```json
{
    "id": 1,
    "action": "block"
}
```

Note: Use "unblock" in the action field to reactivate the client.

### 2.6 Delete Client

Permanently deletes a client. Clients with loans cannot be deleted.

- Method: DELETE
- Endpoint: `/api/clients/{id}`

## 3. Object Types

Definition of equipment categories (e.g., PC models, measuring instruments).

### 3.1 List Types

Retrieves all defined object categories.

- Method: GET
- Endpoint: `/api/objecttypes`

### 3.2 Type Details

Retrieves category information via a unique code.

- Method: GET
- Endpoint: `/api/objecttypes/{code}`
- Example URL: `/api/objecttypes/NB-DELL-XPS15`

### 3.3 Create Type

Creates a new object category.

- Method: POST
- Endpoint: `/api/objecttypes`
- Request Body (JSON):

```json
{
    "code": "PC-HP-G8",
    "name": "HP Elitebook G8",
    "description": "Business Notebook 14 inch",
    "brand": "HP",
    "model": "Elitebook 840 G8"
}
```

### 3.4 Update Type

Modifies the descriptive information of an existing type.

- Method: PUT
- Endpoint: `/api/objecttypes/{code}`
- Request Body (JSON):

```json
{
    "name": "HP Elitebook G8",
    "description": "Business Notebook 14 inch",
    "brand": "HP",
    "model": "Elitebook 840 G8"
}
```

## 4. Inventory Objects (Physical Assets)

Management of individual physical assets stored in the warehouse.

### 4.1 List Objects

Lists all inventoried objects with their type code, location and state.

- Method: GET
- Endpoint: `/api/objects`

### 4.2 List Available Objects

Lists only objects that are not currently on loan or in maintenance (State = 1).

- Method: GET
- Endpoint: `/api/objects/available`

### 4.3 Create Object

Adds a new physical object to the inventory (it starts as Available).

- Method: POST
- Endpoint: `/api/objects`
- Request Body (JSON):

```json
{
    "object_type_code": "PC-HP-G8",
    "location": "Cabinet B - Shelf 2"
}
```

### 4.4 Quick Maintenance Request

Creates a maintenance request for a specific object (same as 6.3).

- Method: POST
- Endpoint: `/api/objects/maintenance`
- Request Body (JSON):

```json
{
    "object_id": 1,
    "technician_id": 2,
    "description": "Screen does not turn on",
    "type": "repair"
}
```

### 4.5 Put Out of Service

Sets the object status to "Out of Service". Only Damaged objects can be put out of service, and it cannot be undone.

- Method: PUT
- Endpoint: `/api/objects/{id}/toggle-service`

### 4.6 Object Details

Retrieves detailed information about a specific physical object via its ID.

- Method: GET
- Endpoint: `/api/objects/{id}`
- Example URL: `/api/objects/1`

### 4.7 Update Object

Updates the location of an existing physical object.

- Method: PUT
- Endpoint: `/api/objects/{id}`
- Request Body (JSON):

```json
{
    "location": "New Location - Lab 3"
}
```

## 5. Loans

Management of asset assignments to clients.

### 5.1 List Loans

Retrieves the loan history, each loan with its loaned objects.

- Method: GET
- Endpoint: `/api/loans`

### 5.2 Loan Details

Retrieves full loan data, including associated objects.

- Method: GET
- Endpoint: `/api/loans/{id}`

### 5.3 Create Loan

Creates a new loan by associating one or more objects (max 5) with a client. The loan lasts at most 30 days, the client must not be blocked and the objects must be available.

- Method: POST
- Endpoint: `/api/loans`
- Request Body (JSON):

```json
{
    "client_id": 1,
    "start_date": "2026-02-01",
    "end_date": "2026-02-08",
    "note": "Loan for conference",
    "inventory_items": [
        {
            "id": 1,
            "object_type_code": "NB-DELL-XPS15"
        }
    ]
}
```

Note: `start_date` is optional (default: today).

### 5.4 Update Loan

Modifies the notes and the objects of an open loan.

- Method: PUT
- Endpoint: `/api/loans/{id}`
- Request Body (JSON):

```json
{
    "note": "Note updated by administration",
    "inventory_items": [
        { "id": 1, "object_type_code": "NB-DELL-XPS15", "state": 4 },
        { "id": 4, "object_type_code": "MS-LOGI-B100" }
    ]
}
```

Note: `inventory_items` is the complete list of the loan's objects. Objects left out are removed from the loan and become available again, so an empty list removes every object. Use `"state": 4` to mark an object as damaged.

### 5.5 Close Loan (Return)

Finalizes the loan, setting the return date to today. Objects become available again, or Damaged if they were marked as damaged.

- Method: POST
- Endpoint: `/api/loans/{id}/close`
- Request Body: None.

## 6. Maintenance

Management of technical interventions on objects.

### 6.1 List Maintenance

Displays all maintenance interventions (open and closed), each with its operations.

- Method: GET
- Endpoint: `/api/maintenance`

### 6.2 Maintenance Details

Displays details of a single intervention, with its operations.

- Method: GET
- Endpoint: `/api/maintenance/{id}`

### 6.3 Start Maintenance

Opens a new maintenance record for an object, which becomes InMaintenance.

- Method: POST
- Endpoint: `/api/maintenance`
- Request Body (JSON):

```json
{
    "object_id": 3,
    "technician_id": 2,
    "description": "Fan cleaning and thermal check",
    "type": "routine"
}
```

### 6.4 Close Maintenance

Ends the maintenance intervention and closes its open operations.

- Method: PUT
- Endpoint: `/api/maintenance/{id}`
- Request Body (JSON):

```json
{
    "damaged": 0
}
```

Note: With `"damaged": 0` the object becomes Available; with `"damaged": 1` it becomes Damaged.

## 7. Operations

Management of specific activities performed during maintenance.

### 7.1 Add Operation

Records a specific task and its cost within an existing maintenance record.

- Method: POST
- Endpoint: `/api/operations`
- Request Body (JSON):

```json
{
    "maintenance_id": 1,
    "description": "Thermal paste replacement",
    "cost": 15.50,
    "start_date": "2026-01-29"
}
```

Note: `start_date` is optional (default: today). The end date can be set with 7.2.

### 7.2 Update Operation

Modifies the description, cost and end date of a previously recorded operation (the start date cannot be changed).

- Method: PUT
- Endpoint: `/api/operations/{id}`
- Request Body (JSON):

```json
{
    "description": "Corrected description",
    "cost": 20.00,
    "end_date": "2026-01-30"
}
```

## 8. Users

### 8.1 List Technicians

Lists the technicians that maintenances can be assigned to.

- Method: GET
- Endpoint: `/api/technicians`
