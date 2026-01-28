# User Manual

### Key Capabilities

- **Inventory Management**: Track all laboratory equipment with detailed information
- **Loan Management**: Create and manage equipment loans with automatic tracking
- **Maintenance Tracking**: Record and monitor equipment maintenance history
- **Client Management**: Manage laboratory clients and their loan records
- **Search & Filtering**: Search and filter capabilities across all modules

---

## User Roles

The system supports two distinct user roles, each with specific permissions and access levels:

### 1. ADMINISTRATOR (Admin)

**Credentials Example**: `admin@library.com` / `admin123`

The Administrator has full access to all features of the system and is responsible for overall management.

#### Administrator Permissions:

| Feature                       |    Admin Access     |
| ----------------------------- | :-----------------: |
| **Loans Module**              |       ✅ Full       |
| **Inventory Module**          |       ✅ Full       |
| **Clients Module**            |       ✅ Full       |
| **Maintenance Module**        | ✅ Full (view only) |
| Create/Edit/Delete Items      |       ✅ Yes        |
| Create/Edit Loans             |       ✅ Yes        |
| Create/Edit Clients           |       ✅ Yes        |
| Manage Equipment Status       |       ✅ Yes        |
| Send Equipment to Maintenance |       ✅ Yes        |
| View All Maintenance Records  |       ✅ Yes        |

#### Administrator Responsibilities:

- **Equipment Management**: Add new equipment types and instances, modify equipment details, manage equipment status
- **Loan Management**: Create loans for clients, process returns, mark items as damaged, block clients if needed
- **Client Management**: Register new clients, update client information, block/unblock clients
- **Maintenance Oversight**: Monitor all maintenance activities, review completed work
- **System Monitoring**: Track loan history, maintenance costs, equipment availability

#### Administrator Dashboard:

When an Admin logs in, they see all four navigation tabs:

- **Loans** - Manage all loan operations
- **Inventory** - Manage all equipment
- **Clients** - Manage all client records
- **Maintenance** - Monitor all maintenance activities

---

### 2. TECHNICIAN (Tech)

**Credentials Examples**:

- `mario.rossi@library.com` / `tech123`
- `sara.bianchi@library.com` / `tech123`
- `roberto.verdi@library.com` / `tech123`
- `anna.neri@library.com` / `tech123`

The Technician has limited access focused on maintenance operations and equipment service.

#### Technician Permissions:

| Feature                       | Technician Access |
| ----------------------------- | :---------------: |
| **Loans Module**              |       ❌ No       |
| **Inventory Module**          |       ❌ No       |
| **Clients Module**            |       ❌ No       |
| **Maintenance Module**        |      ✅ Full      |
| Create/Edit Items             |       ❌ No       |
| Create/Edit Loans             |       ❌ No       |
| Create/Edit Clients           |       ❌ No       |
| View Assigned Maintenance     |      ✅ Yes       |
| Add Operations to Maintenance |      ✅ Yes       |
| Close Maintenance Records     |      ✅ Yes       |
| View All Maintenance Records  |      ✅ Yes       |

#### Technician Responsibilities:

- **Maintenance Operations**: Work on equipment sent for maintenance
- **Operation Management**: Add detailed operations (repairs, replacements, etc.) with costs and dates
- **Maintenance Completion**: Finalize maintenance work and update equipment status
- **Documentation**: Maintain detailed records of work performed and costs incurred

#### Technician Dashboard:

When a Technician logs in, they have access to **ONLY** the Maintenance module:

- **Maintenance** - Manage assigned maintenance work

All other modules (Loans, Inventory, Clients) are hidden from the Technician interface.

---

## Getting Started

### Accessing the Application

1. Open your web browser
2. Navigate to `http://localhost:8080/frontend`
3. You will be presented with the **Login Page**

### Login

1. Enter your **Email** in the email field
2. Enter your **Password** in the password field
3. Click **Login** to access the application

Once logged in, you will see the main navigation bar with access to all modules.

---

## Main Features

### Navigation

The main navigation bar contains buttons for accessing different modules:

- **Loans** - Manage equipment loans and returns
- **Inventory** - View and manage equipment database
- **Clients** - Manage client information
- **Maintenance** - Track and manage equipment maintenance

---

## Module Guide

### 1. LOANS MODULE

The Loans module allows you to create, manage, and track equipment loans.

#### Main View

- **Loans Table**: Displays all loans with the following columns:
  - **Loan ID**: Unique identifier for the loan
  - **Client**: Name of the client borrowing the equipment
  - **Items**: Number of items in the loan
  - **Start Date**: When the loan began
  - **Expected Return**: When the equipment should be returned
  - **Actual Return**: When the equipment was actually returned (if applicable)
  - **Status Badge**: Current status of the loan (Active, Completed, Overdue, Cancelled)

#### Creating a New Loan

1. Click the **"New Loan"** button (appears in toolbar when in Loans view)
2. In the modal that appears:
   - **Select Client**: Choose from the dropdown list of existing clients
   - **Add New Client** (optional): Click the green "+" button to add a new client on the fly
     - Enter First Name and Last Name
     - Enter Email address
     - Click "Create Client"
   - **Select Items**:
     - Click the "Select Items" button to open the item picker
     - Search for items by name
     - Click items to add them (maximum 5 items per loan)
     - Click "Confirm" to finalize selection
   - **Set Loan Start Date**: Select when the loan begins
   - **Set Expected Return Date**: Select when the equipment should be returned (max 30 days from start)
   - **Add Notes** (optional): Add any additional information about the loan
3. Click **"Create Loan"** to save

#### Managing Active Loans

1. Click on any loan row in the table to open the **Loan Detail Modal**
2. In the detail view, you can see:
   - Complete loan information
   - List of loaned items
   - Loan status
   - Associated notes

#### Closing a Loan (Returning Equipment)

1. Open the loan detail modal (click on the loan)
2. Click the **"Close Loan"** button
3. In the close loan dialog:
   - **Add Return Notes** (optional): Document the return condition
   - **Mark Damaged Items** (optional):
     - Click the "Damaged Items" button
     - Select any items that were returned damaged
     - These items will be marked as "Damged" in inventory
   - Click **"Complete Return"** to finalize
4. The loan status will change to "Completed"
5. Inventory counts will be updated automatically

#### Loan Constraints

- **Maximum Items**: 5 items per loan
- **Maximum Duration**: 30 days
- **Restrictions**: Cannot loan items that are in "Damaged", "InMaintenance" or "Out Of Service" status
- **Automatic Notifications**: Clients receive notifications 3 days before loan expiration
- **Client Blocking**: Clients with overdue loans or damaged items cannot create new loans

#### Search & Filtering

- **Search Bar**: Find loans by client name or loan ID
- **Filters** (click Filters button):
  - By Status (Active, Completed, Overdue, Cancelled)
  - By Client
  - By Item Type
  - By Date Range

---

### 2. INVENTORY MODULE

The Inventory module manages all laboratory equipment and equipment types.

#### Main View

- **Inventory Table**: Displays all equipment with:
  - **Name**: Equipment name
  - **Type**: Category of equipment
  - **Status**: Current status (Available, On Loan, In Maintenance, Broken, Out of Service)
  - **Quantity**: Number of units available
  - **Location**: Physical location in the lab
  - **Brand/Model**: Equipment specifications

#### Adding a New Equipment Type

1. Click **"New Item"** button in the toolbar
2. Fill in the equipment type information:
   - **Name**: Name of the equipment type (e.g., "Laptop", "Monitor")
   - **Type**: Category (PC, Component, Peripheral, Other)
   - **Brand**: Manufacturer name
   - **Model**: Model number
   - **Quantity**: How many units to create
   - **Location**: Where it's stored/located
   - **Description**: Additional details
3. Click **"Create Type"**

#### Adding Instances of Equipment Type

1. Find an equipment type in the inventory table
2. Click on it to open the detail modal
3. In the detail view, click **"Add New Instance"**
4. Select the location for this specific unit
5. Click **"Create Instance"**

#### Viewing Equipment Details

1. Click on any item in the inventory table
2. The **Item Detail Modal** opens showing:
   - Equipment ID and basic information
   - Current status and location
   - Brand and model details
   - **Maintenance History**: Table of all maintenance performed
3. From here you can:
   - **Edit Item**: Click "Edit" to modify details
   - **Send to Maintenance**: Mark item for maintenance work

#### Editing Equipment

1. Open item detail modal (click on item)
2. Click **"Edit Item"** button
3. Modify any of the following:
   - Name
   - Brand/Model
   - Location
   - Status (Available, On Loan, In Maintenance, Broken, Out of Service)
   - Notes
4. Click **"Save Changes"**

#### Equipment Status Explained

- **Available**: Equipment is ready to be loaned
- **On Loan**: Equipment is currently being used by a client
- **In Maintenance**: Equipment is being serviced and unavailable
- **Broken**: Equipment is non-functional and cannot be loaned
- **Out of Service**: Equipment is retired or permanently unavailable

#### Search & Filtering

- **Search Bar**: Find equipment by name, code, or model
- **Filters** (click Filters button):
  - By Status
  - By Type
  - By Quantity Available
  - By Location

---

### 3. CLIENTS MODULE

The Clients module manages all laboratory clients.

#### Main View

- **Clients Table**: Lists all registered clients with:
  - **Name**: Full name of the client
  - **Email**: Contact email address
  - **Active Loans**: Number of current active loans
  - **Status**: Blocked or Active

#### Adding a New Client

1. Click **"New Client"** button in the toolbar
2. Fill in the client information:
   - **First Name**: Client's first name
   - **Last Name**: Client's last name
   - **Email**: Email address
3. Click **"Create Client"**

#### Viewing Client Details

1. Click on any client row to see:
   - Client information
   - List of all loans (active and completed)
   - Loan history with dates and items

#### Managing Clients

You can:

- **Edit Information**: Modify client details (name, email)
- **Block/Unblock Client**: Prevent a client from creating new loans
  - Useful when client has damaged items or overdue loans
  - Client can be automatically unblocked after a specified date

#### Search & Filtering

- **Search Bar**: Find clients by name or email
- **Filters** (click Filters button):
  - By Status (Active, Blocked)
  - By Number of Loans

---

### 4. MAINTENANCE MODULE

The Maintenance module tracks all equipment maintenance activities.

#### Main View

- **Maintenance Table**: Shows all maintenance records with:
  - **Maintenance ID**: Unique identifier
  - **Equipment**: Name of equipment being serviced
  - **Status**: Pending, In Progress, Completed
  - **Technician**: Person responsible for maintenance
  - **Start Date**: When work began
  - **End Date**: When work was completed

#### Creating a Maintenance Record

Maintenance records are created when an item is sent to maintenance from the Inventory module:

1. Go to **Inventory**
2. Click on an equipment item
3. Click **"Send to Maintenance"**
4. Fill in:
   - **Description**: Description of the problem or maintenance needed
   - **Technician**: Select the technician responsible
5. Click **"Send to Maintenance"**

#### Viewing Maintenance Details

1. Click on any maintenance record in the table
2. The **Maintenance Detail Modal** shows:
   - Equipment details
   - Technician assigned
   - Problem description
   - Start date
   - **Operations List**: Individual work performed
   - **Total Cost**: Cumulative maintenance cost

#### Adding Operations to Maintenance (Technician Only)

Operations are individual work items within a maintenance record.

1. Open a maintenance detail modal
2. Click **"Add Operation"** button
3. In the operation form, enter:
   - **Operation Description**: What was done (e.g., "Replaced hard drive")
   - **Operation Cost**: Cost of this specific operation
   - **Start Date**: When work on this operation began
   - **End Date**: When work completed
4. Click **"Add Operation"**
5. Operations appear in the list within the maintenance record
6. Total cost is calculated automatically

#### Editing Operations (Technician Only)

1. In maintenance detail, click on an operation to edit it
2. Modify the details
3. Save changes

#### Completing Maintenance (Technician Only)

1. Open the maintenance record detail modal
2. Click **"Complete Maintenance"** button
3. In the confirmation dialog:
   - **Add Notes** (optional): Final notes about the maintenance
   - **Check "Still Broken"** if: Equipment was inspected but found to still be non-functional
     - If unchecked: Equipment status automatically changes to "Available"
     - If checked: Equipment remains "Broken"
4. Click **"Confirm"** to complete

#### Search & Filtering

- **Search Bar**: Find maintenance by equipment name or technician
- **Filters** (click Filters button):
  - By Status (Pending, In Progress, Completed)
  - By Technician
  - By Date Range
  - By Equipment Type

---
