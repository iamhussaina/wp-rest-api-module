# REST API Module for Wordpress

A class-based WordPress module for creating custom CRUD (Create, Read, Update, Delete) REST API endpoints for a Custom Post Type. This module is designed to be included in a WordPress theme.

This module demonstrates:
* A clean, object-oriented (OOP) structure using Singleton patterns.
* Registration of a custom post type ('Books').
* Registration of custom REST API routes for all CRUD operations.
* Robust permission checks for each endpoint.
* Data validation and sanitization.
* Correct use of `WP_REST_Response` and `WP_Error` for professional API responses.

## 📂 Installation

1.  **Download:** Download the `wp-rest-api-module` folder.
2.  **Place Folder:** Place the entire folder inside your active WordPress theme's directory (e.g., `/wp-content/themes/your-theme/`).
3.  **Include Module:** Open your theme's `functions.php` file and add the following line of PHP to include the module's loader:

    ```php
    require_once get_template_directory() . '/wp-rest-api-module/hussainas-rest-api-module.php';
    ```

4.  **Flush Permalinks:** Log in to your WordPress admin, go to **Settings > Permalinks**, and simply click the **Save Changes** button. This flushes the rewrite rules and registers the new CPT and API endpoints.

## Endpoints

This module registers a 'Book' CPT (`hussainas_book`) and exposes the following endpoints under the `hussainas/v1` namespace.

**Base URL:** `https://your-domain.com/wp-json/hussainas/v1`

| Method | Route | Description | Auth Required? |
| :--- | :--- | :--- | :--- |
| `GET` | `/books` | Get a collection of all books. | No |
| `POST` | `/books` | Create a new book. | Yes (Editor role or higher) |
| `GET` | `/books/<id>` | Get a single book by its ID. | No |
| `PUT` / `PATCH` | `/books/<id>` | Update an existing book. | Yes (Must be post author or Editor+) |
| `DELETE` | `/books/<id>` | Delete a book. | Yes (Must be post author or Editor+) |

---

### Example: Creating a Book (POST)

**Endpoint:** `POST /wp-json/hussainas/v1/books`

**Authentication:** You must send an authenticated request. The easiest way during testing is to use the [Application Passwords](https://www.wordpress.org/plugins/application-passwords/) plugin or a [JWT plugin](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/) and provide a valid token or Basic Auth header.

**JSON Body:**
```json
{
    "title": "My New Book Title",
    "content": "This is the content of my new book.",
    "status": "publish"
}
```

**Success Response (201 Created):**
```json
{
    "id": 123,
    "date": "2025-11-13T08:30:00",
    "slug": "my-new-book-title",
    "status": "publish",
    "title": "My New Book Title",
    "content": "<p>This is the content of my new book.</p>",
    "author": 1,
    "_links": {
        "self": [ ... ],
        "collection": [ ... ],
        "author": [ ... ]
    }
}
```

### Example: Updating a Book (PUT)

**Endpoint:** `PUT /wp-json/hussainas/v1/books/123`

**JSON Body:**
```json
{
    "title": "My Updated Book Title"
}
```

### Example: Deleting a Book (DELETE)

**Endpoint:** `DELETE /wp-json/hussainas/v1/books/123`

This will move the book to the trash. To permanently delete it, add the `force` parameter:

`DELETE /wp-json/hussainas/v1/books/123?force=true`

## 🔧 Customization

To adapt this module for your own needs (e.g., for a 'Movies' CPT instead of 'Books'):

1.  **Search & Replace:**
    * `Hussainas_` -> `YourPrefix_`
    * `hussainas_` -> `your_prefix_`
    * `hussainas/v1` -> `your-namespace/v1`
2.  **CPT Controller (`class-hussainas-cpt-controller.php`):**
    * Change `$post_type = 'hussainas_book'` to `$post_type = 'your_cpt_slug'`.
    * Update the CPT labels and args in the `register_post_type` method.
    * Update `'rest_base' => 'books'` to `'rest_base' => 'your-rest-base'`.
3.  **API Controller (`class-hussainas-api-controller.php`):**
    * Change `$rest_base = 'books'` to `$rest_base = 'your-rest-base'`.
    * Change `$post_type = 'hussainas_book'` to `$post_type = 'your_cpt_slug'`.
    * Update the schema in `get_public_item_schema()` to reflect your CPT's data fields.
    * Update the `prepare_item_for_response()` method to format your custom fields.
