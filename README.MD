# Shared Lists – Task & List Management System

## 1. Project Overview

Shared Lists is a cross-platform application for creating, organizing, and sharing lists between multiple users (family members, students, teams). The system allows users to work on the same list in real time, organizes categories, and includes automated helpers like merging duplicate items and escalating overdue tasks.

---

## 2. Target Audience & Use Cases

- **Households:** Shared shopping and grocery lists, assigning household chores.
- **Students:** Group assignments, shared study plans, tracking deadlines.
- **Teams:** Simple task delegation, tracking work deliverables.
- **Events & Travel:** Vacation packing lists, party planning, and gift ideas.

---

## 3. Core Features

### 3.1. Lists & Categories

- **Lists:** Each list has a title, description, category, creation date, and an owner.
- **Categories:** Default categories (Shopping, Groceries, Household Chores, Travel, Study, Work, Events, Gift Ideas, Personal) plus support for custom categories created by users.
- **Filtering & Search:** Search lists by name, filter by category, and sort by date or name.

### 3.2. Items & Automation

- **Item Fields:** Name, description, quantity, unit (kg, pcs), priority (Low, Medium, High), status (Pending, Completed, Overdue), due date, notes, and assigned user.
- **Smart Deduplication:** If a user adds an item with the same name and unit as an existing pending item, the app automatically increases the quantity instead of making a duplicate.
- **Overdue Tasks:** When the due date passes and the task is still pending, the app marks it as Overdue and increases priority by one level (e.g., Low becomes Medium).
- **Recurring Items:** Tasks with a set frequency automatically reset back to Pending for the next cycle after being completed.

### 3.3. Collaboration & Real-Time Sync

- **Invitations & Roles:** Owners invite collaborators by email. Roles are Viewer (read-only) and Editor (can add, edit, and delete items).
- **Real-Time Polling:** The app automatically checks for updates in the background every few seconds so users see changes without manual page reloads.
- **Conflict Handling:** The system uses timestamps to check if another user modified an item before saving changes.

### 3.4. Extra Views

- **My Items:** A single screen showing all items assigned to the logged-in user across all lists, sorted by deadline.
- **Calendar:** Week and month view to see scheduled tasks and change due dates.

---

## 4. Technology Stack

| Platform / Tier     | Technology              | Reason for Selection                                                                 |
| :------------------ | :---------------------- | :----------------------------------------------------------------------------------- |
| **Web Frontend**    | HTML5, CSS3, JavaScript | Lightweight, native browser support, Fetch API for simple communication with backend |
| **Mobile**          | Capacitor               | Allows packaging the web code into an Android/iOS mobile application                 |
| **Desktop**         | Electron                | Wraps the application for desktop OS with system notifications                       |
| **Backend API**     | PHP 8.x                 | Efficient REST API processing JSON requests                                          |
| **Database**        | MariaDB                 | Reliable relational database with foreign key support                                |
| **Database Access** | PDO                     | Prepared statements to securely prevent SQL Injection                                |

---

## 5. System Architecture & Diagrams

### 5.1. Overall Architecture (Three-Tier Model)

Shows the communication flow between the frontend clients (Web, Electron, Capacitor), the PHP REST API, and MariaDB.

```mermaid
graph TD
    subgraph PresentationTier["Presentation Tier (Clients)"]
        Web["Web Browser<br/>(HTML5 / CSS / JS)"]
        Desktop["Desktop App<br/>(Electron)"]
        Mobile["Mobile App<br/>(Capacitor)"]
    end

    Web -->|HTTPS / JSON| PHP
    Desktop -->|HTTPS / JSON| PHP
    Mobile -->|HTTPS / JSON| PHP

    subgraph BusinessLogicTier["Business Logic Tier (PHP 8.x)"]
        PHP["API Router & Endpoints"]
        Modules["Controllers & Services<br/>(Auth, Lists, Items, Automation, Sync)"]
        PDO["PDO Layer"]
    end

    PHP --- Modules
    Modules --- PDO

    subgraph DatabaseTier["Database Tier (MariaDB)"]
        DB[("MariaDB Database<br/>users, categories, lists,<br/>list_collaborators, items")]
    end

    PDO ==>|Prepared Statements| DB
```

### 5.2 Database Schema (Entity-Relationship Diagram)

Shows the structure of the MariaDB database with all 5 tables, their fields, and the relationships between them.

```mermaid
erDiagram
    USERS ||--o{ LISTS : "owns"
    USERS ||--o{ CATEGORIES : "creates"
    USERS ||--o{ LIST_COLLABORATORS : "collaborates"
    USERS ||--o{ ITEMS : "assigned_to"

    CATEGORIES ||--o{ LISTS : "groups"

    LISTS ||--o{ LIST_COLLABORATORS : "shares"
    LISTS ||--o{ ITEMS : "contains"

    USERS {
        int id PK
        string email
        string password_hash
        string name
        datetime created_at
    }

    CATEGORIES {
        int id PK
        int user_id FK "nullable"
        string name
        datetime created_at
    }

    LISTS {
        int id PK
        int owner_id FK
        int category_id FK
        string name
        text description
        datetime created_at
        timestamp updated_at
    }

    LIST_COLLABORATORS {
        int id PK
        int list_id FK
        int user_id FK "nullable"
        string invited_email
        string role
        string status
        datetime created_at
    }

    ITEMS {
        int id PK
        int list_id FK
        int assigned_to_user_id FK "nullable"
        string name
        decimal quantity
        string unit
        string priority
        string status
        datetime due_date "nullable"
        text notes "nullable"
        boolean is_recurring
        string recurring_frequency "nullable"
        datetime created_at
        timestamp updated_at
    }
```

---

### 5.3 Backend Class Diagram (PHP 8.x)

Shows the main backend classes in PHP, including controllers for API routing, services for automation/sync logic, and database access via PDO.

```mermaid
classDiagram
    direction TB

    class Database {
        -PDO connection
        +getInstance() Database
        +getConnection() PDO
    }

    class AuthController {
        -PDO db
        +register(email, password, name)
        +login(email, password)
        +validateSession()
        +checkPermission(userId, listId, role)
    }

    class ListController {
        -PDO db
        +getLists(userId)
        +createList(userId, name, categoryId)
        +updateList(listId, userId, data)
        +deleteList(listId, userId)
        +inviteCollaborator(listId, email, role)
    }

    class ItemController {
        -PDO db
        -AutomationService autoService
        -SyncService syncService
        +getItems(listId)
        +getMyItems(userId)
        +getCalendarItems(userId, startDate, endDate)
        +addItem(listId, data)
        +updateItem(itemId, data, clientTimestamp)
        +deleteItem(itemId)
    }

    class AutomationService {
        -PDO db
        +mergeDuplicateItems(listId, name, unit, qty)
        +checkAndEscalateOverdue(listId)
        +processRecurrence(itemId)
    }

    class SyncService {
        -PDO db
        +checkOptimisticLock(itemId, clientTimestamp)
        +getChangesSince(listId, lastTimestamp)
    }

    AuthController ..> Database : uses
    ListController ..> Database : uses
    ItemController ..> Database : uses
    ItemController --> AutomationService : uses
    ItemController --> SyncService : uses
    AutomationService ..> Database : uses
    SyncService ..> Database : uses
```

---

### 5.4 Frontend & Platform Class Diagram (JS / Electron / Capacitor)

Shows the client-side JavaScript structure, including the UI renderer, the API client, background polling, and native wrappers for Electron and Capacitor.

```mermaid
classDiagram
    direction TB

    class UIManager {
        -ApiClient api
        -SyncManager sync
        +init()
        +renderLists(lists)
        +renderItems(items)
        +renderCalendar(items)
        +showNotification(message)
    }

    class ApiClient {
        -string baseUrl
        +get(endpoint, params)
        +post(endpoint, body)
        +put(endpoint, body)
        +delete(endpoint)
    }

    class SyncManager {
        -int interval
        -string lastSyncTime
        +startPolling(listId)
        +stopPolling()
        +checkForUpdates()
    }

    class DesktopBridge {
        +registerShortcuts()
        +showDesktopNotification(title, text)
    }

    class MobileBridge {
        +triggerHaptic()
        +showLocalNotification(title, text)
    }

    UIManager --> ApiClient : uses
    UIManager --> SyncManager : uses
    SyncManager --> ApiClient : calls
    UIManager ..> DesktopBridge : uses on desktop
    UIManager ..> MobileBridge : uses on mobile
```

---

### 5.5 Sequence Diagram: Adding an Item with Deduplication

Shows the step-by-step process of adding a new item, including the database check to merge quantities if an identical pending item already exists.

```mermaid
sequenceDiagram
    autonumber
    actor User as User
    participant UI as UIManager (JS)
    participant API as ApiClient (Fetch)
    participant Ctrl as ItemController (PHP)
    participant Auto as AutomationService
    participant DB as MariaDB (PDO)

    User->>UI: Types item ("Milk", 2, "pcs") & clicks Add
    UI->>API: post('/api/items.php', data)
    API->>Ctrl: POST request with JSON
    Ctrl->>Auto: mergeDuplicateItems(listId, "Milk", "pcs", 2)
    Auto->>DB: Check if pending item exists

    alt Item already exists
        DB-->>Auto: Found item
        Auto->>DB: UPDATE quantity = quantity + 2
        Auto-->>Ctrl: Merged successfully
    else New item
        DB-->>Auto: Not found
        Auto-->>Ctrl: Not merged
        Ctrl->>DB: INSERT INTO items (...)
    end

    Ctrl-->>API: Return JSON response
    API-->>UI: Update complete
    UI->>User: Refresh list on screen
```
