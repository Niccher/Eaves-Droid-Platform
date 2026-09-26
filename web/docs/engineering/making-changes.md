# Engineering: Making Changes and Recipes

This guide outlines common development workflows and the Definition of Done for modifying the CodeIgniter 4 backend.

---

## 1. Task Routing Matrix

| If you want to… | Files to modify |
|-----------------|-----------------|
| Add a forensic data page | Controller in `app/Controllers/Client/`, View in `app/Views/pages/`, Route in `app/Config/Routes.php` |
| Add a new parser category | Add handler in `app/Modules/Mod_Parse_Advanced.php`, update table schema |
| Add an administrative view | Controller in `app/Controllers/Admin/`, update `RoleFilter` group check |
| Gate a feature by subscription | Use `PlanGate::hasFeature('feature_name')` in controller or view |
| Add a database column | Create migration via `php spark make:migration`, update Model |

---

## 2. Common Recipes

### Adding a New Route and Controller
1. Define the route in `app/Config/Routes.php`:
   ```php
   $routes->group('reports', ['filter' => 'role:user'], static function ($routes) {
       $routes->get('summary', 'Client\Reports::summary');
   });
   ```
2. Create the controller in `app/Controllers/Client/Reports.php`:
   ```php
   namespace App\Controllers\Client;
   use App\Controllers\BaseController;

   class Reports extends BaseController {
       public function summary() {
           return view('pages/reports_summary', ['title' => 'Report Summary']);
       }
   }
   ```

### Creating and Running a Migration
```bash
php spark make:migration AddStatusToDevices
# Edit the created file in app/Database/Migrations/
php spark migrate
```

---

## 3. Definition of Done (Contract Changes)

Before opening a pull request for a feature affecting cross-stack communication:
- [ ] Code implemented and tested locally.
- [ ] Database migration written in `app/Database/Migrations/`.
- [ ] If API request/response format changed, update Android models and ML engine schemas.
- [ ] `.env.example` / `env` updated if a new configuration variable is introduced.
- [ ] Relevant documentation updated in `docs/`.
