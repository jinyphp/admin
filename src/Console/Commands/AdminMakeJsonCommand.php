<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class AdminMakeJsonCommand extends Command
{
    protected $signature = 'admin:make-json {module} {controller} {--force : Overwrite existing JSON file}';
    protected $description = 'Generate JSON configuration file for admin controller';

    public function handle()
    {
        $module = $this->argument('module');
        $controller = $this->argument('controller');
        $force = $this->option('force');

        $moduleUC = ucfirst($module);
        $moduleLC = strtolower($module);
        $controllerName = 'Admin' . ucfirst($controller);
        $modelName = 'Admin' . ucfirst($controller);
        $tableName = 'admin_' . Str::snake(Str::plural($controller));
        $viewPath = 'admin_' . Str::snake($controller);
        $routeName = Str::snake($controller, '.');
        $routePrefix = str_replace('.', '/', Str::snake($controller, '/'));

        $targetPath = base_path("jiny/{$module}/App/Http/Controllers/Admin/{$controllerName}/{$controllerName}.json");

        if (File::exists($targetPath) && !$force) {
            $this->error("JSON file already exists: {$targetPath}");
            $this->info('Use --force option to overwrite');
            return 1;
        }

        $stubPath = base_path('jiny/admin/stubs/controller/Admin.json.stub');
        if (!File::exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");
            return 1;
        }

        $directory = dirname($targetPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $stubContent = File::get($stubPath);

        $replacements = [
            '{{title}}' => ucfirst($controller) . ' Management',
            '{{subtitle}}' => 'Manage ' . Str::plural(strtolower($controller)) . ' in the system',
            '{{description}}' => ucfirst($controller) . ' management system with CRUD operations.',
            '{{routeName}}' => $routeName,
            '{{routePrefix}}' => $routePrefix,
            '{{tableName}}' => $tableName,
            '{{Module}}' => $moduleUC,
            '{{moduleLC}}' => $moduleLC,
            '{{modelName}}' => $modelName,
            '{{viewPath}}' => $viewPath,
            '{{softDeletes}}' => 'false',
            '{{fillable}}' => json_encode(['name', 'description', 'status'], JSON_PRETTY_PRINT),
            '{{guarded}}' => json_encode(['id', 'created_at', 'updated_at'], JSON_PRETTY_PRINT),
            '{{hidden}}' => json_encode([]),
            '{{casts}}' => json_encode(['status' => 'boolean', 'created_at' => 'datetime', 'updated_at' => 'datetime'], JSON_PRETTY_PRINT),
            '{{searchable}}' => json_encode(['name', 'description'], JSON_PRETTY_PRINT),
            '{{sortable}}' => json_encode(['id', 'name', 'status', 'created_at', 'updated_at'], JSON_PRETTY_PRINT),
            '{{filterable}}' => json_encode(['status'], JSON_PRETTY_PRINT),
            '{{indexTitle}}' => ucfirst($controller) . ' List',
            '{{indexDescription}}' => 'Manage your ' . Str::plural(strtolower($controller)),
            '{{perPage}}' => '20',
            '{{perPageOptions}}' => json_encode([10, 25, 50, 100]),
            '{{sortDefault}}' => 'created_at',
            '{{sortDirection}}' => 'desc',
            '{{searchPlaceholder}}' => 'Search ' . Str::plural(strtolower($controller)) . '...',
            '{{filters}}' => $this->generateFilters(),
            '{{tableColumns}}' => $this->generateTableColumns($controller),
            '{{createTitle}}' => 'Create New ' . ucfirst($controller),
            '{{createDescription}}' => 'Add a new ' . strtolower($controller) . ' to the system',
            '{{createButtonText}}' => 'Add New ' . ucfirst($controller),
            '{{editTitle}}' => 'Edit ' . ucfirst($controller),
            '{{editDescription}}' => 'Update ' . strtolower($controller) . ' information',
            '{{showTitle}}' => ucfirst($controller) . ' Details',
            '{{showDescription}}' => 'View ' . strtolower($controller) . ' information',
            '{{booleanTrue}}' => 'Yes',
            '{{booleanFalse}}' => 'No',
            '{{deleteConfirmMessage}}' => 'Are you sure you want to delete this ' . strtolower($controller) . '?',
            '{{deleteSuccessMessage}}' => ucfirst($controller) . ' deleted successfully.',
            '{{deleteErrorMessage}}' => 'Error deleting ' . strtolower($controller) . ': %s',
            '{{deleteConfirmRequired}}' => 'Delete confirmation required.',
            '{{storeDefaults}}' => json_encode(['status' => true], JSON_PRETTY_PRINT),
            '{{storeSuccessMessage}}' => ucfirst($controller) . ' created successfully.',
            '{{storeErrorMessage}}' => 'Error creating ' . strtolower($controller) . '.',
            '{{storeContinueMessage}}' => ucfirst($controller) . ' created. You can continue adding more.',
            '{{storeValidationRules}}' => $this->generateValidationRules(),
            '{{storeValidationMessages}}' => $this->generateValidationMessages(),
            '{{updateSuccessMessage}}' => ucfirst($controller) . ' updated successfully.',
            '{{updateErrorMessage}}' => 'Error updating ' . strtolower($controller) . '.',
            '{{updateValidationRules}}' => $this->generateValidationRules(),
            '{{updateValidationMessages}}' => $this->generateValidationMessages(),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stubContent);

        $content = preg_replace('/"\[\s*\n\s*([^\]]+)\s*\n\s*\]"/m', '[$1]', $content);
        $content = preg_replace('/"\{([^}]*)\}"/m', '{$1}', $content);

        File::put($targetPath, $content);

        $this->info("JSON configuration created successfully!");
        $this->info("Path: {$targetPath}");
        $this->newLine();
        $this->info("You can now customize the JSON file to match your requirements.");

        return 0;
    }

    private function generateFilters()
    {
        $filters = [
            'status' => [
                'label' => 'Status',
                'options' => [
                    '' => 'All',
                    '1' => 'Active',
                    '0' => 'Inactive'
                ]
            ]
        ];

        return json_encode($filters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function generateTableColumns($controller)
    {
        $columns = [
            'checkbox' => [
                'visible' => true,
                'width' => '50px'
            ],
            'id' => [
                'label' => 'ID',
                'visible' => true,
                'sortable' => true,
                'width' => '80px'
            ],
            'name' => [
                'label' => 'Name',
                'visible' => true,
                'sortable' => true,
                'searchable' => true
            ],
            'description' => [
                'label' => 'Description',
                'visible' => true,
                'responsive' => 'lg',
                'truncate' => 50
            ],
            'status' => [
                'label' => 'Status',
                'visible' => true,
                'sortable' => true,
                'toggleable' => true
            ],
            'created_at' => [
                'label' => 'Created',
                'visible' => true,
                'sortable' => true,
                'responsive' => 'md',
                'format' => 'Y-m-d'
            ],
            'actions' => [
                'label' => 'Actions',
                'visible' => true,
                'width' => '150px'
            ]
        ];

        return json_encode($columns, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function generateValidationRules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'boolean'
        ];

        return json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function generateValidationMessages()
    {
        $messages = [
            'name.required' => 'Name is required.',
            'name.max' => 'Name must not exceed 255 characters.'
        ];

        return json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}