<?php

namespace Abrachist\Webadmin\Commands\TransactionGenerator;

use File;
use Illuminate\Console\Command;

class GenerateTransactionView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cyom:transaction-view
                            {name : The name of the Crud.}
                            {--fields= : The fields name for the form.}
                            {--fields2= : The fields name for the child form.}
                            {--view-path= : The name of the view path.}
                            {--route-group= : Prefix of the route group.}
                            {--pk=id : The name of the primary key.}
                            {--validations= : Validation details for the fields.}
                            {--localize=no : Localize the view? yes|no.}
                            {--foreign-keys= : The foreign keys for the table.}
                            {--foreign-keys2= : The foreign keys for the child table.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create views for the Crud.';

    /**
     * View Directory Path.
     *
     * @var string
     */
    protected $viewDirectoryPath;

    /**
     *  Form field types collection.
     *
     * @var array
     */
    protected $typeLookup = [
        'string' => 'text',
        'char' => 'text',
        'varchar' => 'text',
        'text' => 'textarea',
        'mediumtext' => 'textarea',
        'longtext' => 'textarea',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'binary' => 'textarea',
        'password' => 'password',
        'email' => 'email',
        'number' => 'number',
        'integer' => 'number',
        'bigint' => 'number',
        'mediumint' => 'number',
        'tinyint' => 'number',
        'smallint' => 'number',
        'decimal' => 'number',
        'double' => 'number',
        'float' => 'number',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'timestamp' => 'datetime-local',
        'time' => 'time',
        'boolean' => 'radio',
        'enum' => 'select',
        'select' => 'select',
        'file' => 'file',
    ];

    /**
     * Form's fields.
     *
     * @var array
     */
    protected $formFields = [];
    protected $formFields2 = [];

    /**
     * Html of Form's fields.
     *
     * @var string
     */
    protected $formFieldsHtml = '';
    protected $formFieldsHtml2 = '';
    protected $formFieldsHtmlHeader2 = '';
    protected $formFieldsHtmlFooter2 = '';
    protected $formDynamicTable = '';
    protected $formSelectFields = '';
    protected $formEditLoadDetail = '';


    /**
     * Number of columns to show from the table. Others are hidden.
     *
     * @var integer
     */
    protected $defaultColumnsToShow = 3;

    /**
     * Name of the Crud.
     *
     * @var string
     */
    protected $crudName = '';

    /**
     * Crud Name in capital form.
     *
     * @var string
     */
    protected $crudNameCap = '';

    /**
     * Crud Name in singular form.
     *
     * @var string
     */
    protected $crudNameSingular = '';

    /**
     * Primary key of the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Name of the Model.
     *
     * @var string
     */
    protected $modelName = '';

    /**
     * Name of the View Dir.
     *
     * @var string
     */
    protected $viewName = '';

    /**
     * Name or prefix of the Route Group.
     *
     * @var string
     */
    protected $routeGroup = '';

    /**
     * Html of the form heading.
     *
     * @var string
     */
    protected $formHeadingHtml = '';

    /**
     * Html of the form body.
     *
     * @var string
     */
    protected $formBodyHtml = '';

    /**
     * Html of view to show.
     *
     * @var string
     */
    protected $formBodyHtmlForShowView = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->viewDirectoryPath = config('cyom.custom_template')
        ? config('cyom.path')
        : __DIR__ . '/../../template/transaction/';

        if (config('cyom.view_columns_number')) {
            $this->defaultColumnsToShow = config('cyom.view_columns_number');
        }
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->crudName = strtolower($this->argument('name'));
        $this->crudNameCap = ucwords($this->crudName);
        $this->crudNameSingular = str_singular($this->crudName);
        $this->modelName = str_singular($this->argument('name'));
        $this->primaryKey = $this->option('pk');
        $this->routeGroup = ($this->option('route-group')) ? $this->option('route-group') . '/' : $this->option('route-group');
        $this->viewName = snake_case($this->argument('name'), '-');

        $viewDirectory = config('view.paths')[0] . '/';
        if ($this->option('view-path')) {
            $this->userViewPath = $this->option('view-path');
            $path = $viewDirectory . $this->userViewPath . '/' . $this->viewName . '/';
        } else {
            $path = $viewDirectory . $this->viewName . '/';
        }

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $fields = $this->option('fields');
        $fieldsArray = explode(';', $fields);

        $this->formFields = [];

        $validations = $this->option('validations');

        if ($fields) {
            $x = 0;
            foreach ($fieldsArray as $item) {
                $itemArray = explode('#', $item);

                $this->formFields[$x]['name'] = trim($itemArray[0]);
                $this->formFields[$x]['type'] = trim($itemArray[1]);
                $this->formFields[$x]['required'] = preg_match('/' . $itemArray[0] . '/', $validations) ? true : false;

                if ($this->formFields[$x]['type'] == 'select' && isset($itemArray[2])) {
                    $options = trim($itemArray[2]);
                    $options = str_replace('options=', '', $options);
                    $optionsArray = explode(',', $options);

                    $commaSeparetedString = implode("', '", $optionsArray);
                    $options = "['" . $commaSeparetedString . "']";

                    $this->formFields[$x]['options'] = $options;
                }

                $x++;
            }
        }

        // set child input form field and table header footer
        $fields2 = $this->option('fields2');
        $fieldsArray2 = explode(';', $fields2);
        $foreignKeys2 = explode(';',$this->option('foreign-keys2'));
        $fkey = [];

        foreach ($foreignKeys2 as $value) {
            $itemArray = explode('#', $value);
            $fkey[] = trim($itemArray[0]);
        }

        $this->formFields2 = [];

        if ($fields2) {
            $x = 0;
            foreach ($fieldsArray2 as $item) {
                $itemArray = explode('#', $item);

                $this->formFields2[$x]['name'] = trim($itemArray[0]);
                $this->formFields2[$x]['type'] = trim($itemArray[1]);
                $this->formFields2[$x]['formcontrol'] = "";

                $fk = array_search(trim($itemArray[0]), $fkey);

                if($fk && $x > 0) {
                    $this->formFields2[$x]['formcontrol'] = 'select';
                }

                if($x > 0) {
                    $this->formFieldsHtmlHeader2 .= '<th>'. camel_case(trim($itemArray[0])) . '</th> ';
                }

                if($x > 1) {
                    $this->formFieldsHtmlFooter2 .= '<th></th> ';
                }
                
                $x++;
            }
        }

        $this->formFieldsHtmlHeader2 .= '<th></th> ';
        $this->formFieldsHtmlFooter2 .= '<th></th> ';

        $this->formFieldsHtml2 .= "' ";
        $this->formDynamicTable .= "<tr> ";
        $x = 0;
        // set array add items
        foreach ($this->formFields2 as $item) {
            $tagOpen = '<input ';
            $tagClose = ' />';
            $attribute = '';
            $id = 'id="' . $item['name'] . "-' + counter + '\" ";
            $name = 'name="' . $item['name'] . '[]"';
            $class = 'class="form-control"';

            $tagOpenColumn = '<td> ';
            $tagCloseColumn = ' </td>';
            $id2 = 'id="' . $item['name'] . '-{{$counter}}"';
            $value = 'value="{{ $detail->' . $item['name'] . '}}"';
            $option = '';

            if($item['formcontrol'] == 'select'){
                $tagOpen = '<select ';
                $tagClose = ' </select>';
                $attribute = 'data-placeholder="Choose one.."';
                $class = 'class="form-control selectize"';
                $option = ' ><option value="{{$detail->' . $item['name'] . '}}"><?php $column = $detail->'. str_replace("_id","",$item['name']) . '->getTableColumns(); ?> {{$detail->'. str_replace("_id","",$item['name']) . '->$column}}</option> ';

                $this->formSelectFields .= "initSelectize('". $item['name'] . "-' + counter); loadSelectizeList('". $item['name'] . "-' + counter, \"{{URL::to('" . str_replace("_id","",$item['name']) . "/list')}}\" );" ;

                $this->formEditLoadDetail .= "loadAllSelectizeList('". $item['name'] . "-', counter, \"{{URL::to('" . str_replace("_id","",$item['name']) . "/list')}}\" );" ;
            }

            if($x > 0){
                $this->formFieldsHtml2 .= $tagOpen . ' ' . $id . ' ' . $name . ' ' . $class . ' ' . $attribute .  $tagClose . "', '";

                $this->formDynamicTable .= $tagOpenColumn . $tagOpen . ' ' . $id2 . ' ' . $name . ' ' . $class . ' ' . $attribute . $value .  $option . $tagClose . $tagCloseColumn ;
            }            

            $x++;
        }

        $this->formFieldsHtml2 .= "<a href=\"javascript:void(0)\" class=\"remove-row btn btn-danger\"><i class=\"fa fa-trash\"></i></a> '";
        $this->formDynamicTable .= " <td><a href=\"javascript:void(0)\" class=\"remove-row btn btn-danger\"><i class=\"fa fa-trash\"></i></a></td></tr>";

        
        foreach ($this->formFields as $item) {
            $this->formFieldsHtml .= $this->createField($item);
        }

        $i = 0;
        foreach ($this->formFields as $key => $value) {
            if ($i == $this->defaultColumnsToShow) {
                break;
            }

            $field = $value['name'];
            $label = ucwords(str_replace('_', ' ', $field));
            if ($this->option('localize') == 'yes') {
                $label = '{{ trans(\'' . $this->crudName . '.' . $field . '\') }}';
            }
            $this->formHeadingHtml .= '<th>' . $label . '</th>';
            $this->formBodyHtml .= '<td>{{ $items->' . $field . ' }}</td>';
            $this->formBodyHtmlForShowView .= '<tr><th> ' . $label . ' </th><td> {{ $%%crudNameSingular%%->' . $field . ' }} </td></tr>';

            $i++;
        }

        // For index.blade.php file
        $indexFile = $this->viewDirectoryPath . 'index.blade.stub';
        $newIndexFile = $path . 'index.blade.php';
        if (!File::copy($indexFile, $newIndexFile)) {
            echo "failed to copy $indexFile...\n";
        } else {
            $this->templateIndexVars($newIndexFile);
        }

        // For form.blade.php file
        $formFile = $this->viewDirectoryPath . 'form.blade.stub';
        $newFormFile = $path . 'form.blade.php';
        if (!File::copy($formFile, $newFormFile)) {
            echo "failed to copy $formFile...\n";
        } else {
            $this->templateFormVars($newFormFile);
        }

        // For create.blade.php file
        $createFile = $this->viewDirectoryPath . 'create.blade.stub';
        $newCreateFile = $path . 'create.blade.php';
        if (!File::copy($createFile, $newCreateFile)) {
            echo "failed to copy $createFile...\n";
        } else {
            $this->templateCreateVars($newCreateFile);
        }

        // For edit.blade.php file
        $editFile = $this->viewDirectoryPath . 'edit.blade.stub';
        $newEditFile = $path . 'edit.blade.php';
        if (!File::copy($editFile, $newEditFile)) {
            echo "failed to copy $editFile...\n";
        } else {
            $this->templateEditVars($newEditFile);
        }

        // For show.blade.php file
        $showFile = $this->viewDirectoryPath . 'show.blade.stub';
        $newShowFile = $path . 'show.blade.php';
        if (!File::copy($showFile, $newShowFile)) {
            echo "failed to copy $showFile...\n";
        } else {
            $this->templateShowVars($newShowFile);
        }

        // For _modal.blade.php file
        $showFile = $this->viewDirectoryPath . '_modal.blade.stub';
        $newShowFile = $path . '_modal.blade.php';
        if (!File::copy($showFile, $newShowFile)) {
            echo "failed to copy $showFile...\n";
        } else {
            $this->templateShowVars($newShowFile);
        }

        $this->info('View created successfully.');
    }

    /**
     * Update values between %% with real values in index view.
     *
     * @param  string $newIndexFile
     *
     * @return void
     */
    public function templateIndexVars($newIndexFile)
    {
        File::put($newIndexFile, str_replace('%%formHeadingHtml%%', $this->formHeadingHtml, File::get($newIndexFile)));
        File::put($newIndexFile, str_replace('%%formBodyHtml%%', $this->formBodyHtml, File::get($newIndexFile)));
        File::put($newIndexFile, str_replace('%%crudName%%', $this->crudName, File::get($newIndexFile)));
        File::put($newIndexFile, str_replace('%%crudNameCap%%', $this->crudNameCap, File::get($newIndexFile)));
        File::put($newIndexFile, str_replace('%%modelName%%', $this->modelName, File::get($newIndexFile)));
        File::put($newIndexFile, str_replace('%%viewName%%', $this->viewName, File::get($newIndexFile)));
        File::put($newIndexFile, str_replace('%%routeGroup%%', $this->routeGroup, File::get($newIndexFile)));
        File::put($newIndexFile, str_replace('%%primaryKey%%', $this->primaryKey, File::get($newIndexFile)));
    }

    /**
     * Update values between %% with real values in form view.
     *
     * @param  string $newFormFile
     *
     * @return void
     */
    public function templateFormVars($newFormFile)
    {
        File::put($newFormFile, str_replace('%%formFieldsHtml%%', $this->formFieldsHtml, File::get($newFormFile)));
    }

    /**
     * Update values between %% with real values in create view.
     *
     * @param  string $newCreateFile
     *
     * @return void
     */
    public function templateCreateVars($newCreateFile)
    {
        $viewTemplateDir = isset($this->userViewPath) ? $this->userViewPath . '.' . $this->viewName : $this->viewName;

        File::put($newCreateFile, str_replace('%%crudName%%', $this->crudName, File::get($newCreateFile)));
        File::put($newCreateFile, str_replace('%%crudNameCap%%', $this->crudNameCap, File::get($newCreateFile)));
        File::put($newCreateFile, str_replace('%%modelName%%', $this->modelName, File::get($newCreateFile)));
        File::put($newCreateFile, str_replace('%%viewName%%', $this->viewName, File::get($newCreateFile)));
        File::put($newCreateFile, str_replace('%%routeGroup%%', $this->routeGroup, File::get($newCreateFile)));
        File::put($newCreateFile, str_replace('%%viewTemplateDir%%', $viewTemplateDir, File::get($newCreateFile)));
        File::put($newCreateFile, str_replace('%%detailContent%%', $this->formFieldsHtml2, File::get($newCreateFile)));
        File::put($newCreateFile, str_replace('%%initAndLoadSelectize%%', $this->formSelectFields, File::get($newCreateFile)));  
        File::put($newCreateFile, str_replace('%%fieldsHtmlHeader%%', $this->formFieldsHtmlHeader2, File::get($newCreateFile)));
        File::put($newCreateFile, str_replace('%%fieldsHtmlFooter%%', $this->formFieldsHtmlFooter2, File::get($newCreateFile)));  
    }

    /**
     * Update values between %% with real values in edit view.
     *
     * @param  string $newEditFile
     *
     * @return void
     */
    public function templateEditVars($newEditFile)
    {
        $viewTemplateDir = isset($this->userViewPath) ? $this->userViewPath . '.' . $this->viewName : $this->viewName;

        File::put($newEditFile, str_replace('%%crudName%%', $this->crudName, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%crudNameSingular%%', $this->crudNameSingular, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%crudNameCap%%', $this->crudNameCap, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%modelName%%', $this->modelName, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%viewName%%', $this->viewName, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%routeGroup%%', $this->routeGroup, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%primaryKey%%', $this->primaryKey, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%viewTemplateDir%%', $viewTemplateDir, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%detailContent%%', $this->formFieldsHtml2, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%initAndLoadSelectize%%', $this->formSelectFields, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%formDynamicTable%%', $this->formDynamicTable, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%fieldsHtmlHeader%%', $this->formFieldsHtmlHeader2, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%fieldsHtmlFooter%%', $this->formFieldsHtmlFooter2, File::get($newEditFile)));
        File::put($newEditFile, str_replace('%%formEditLoadDetail%%', $this->formEditLoadDetail, File::get($newEditFile)));

    }

    /**
     * Update values between %% with real values in show view.
     *
     * @param  string $newShowFile
     *
     * @return void
     */
    public function templateShowVars($newShowFile)
    {
        File::put($newShowFile, str_replace('%%formHeadingHtml%%', $this->formHeadingHtml, File::get($newShowFile)));
        File::put($newShowFile, str_replace('%%formBodyHtmlForShowView%%', $this->formBodyHtmlForShowView, File::get($newShowFile)));
        File::put($newShowFile, str_replace('%%crudName%%', $this->crudName, File::get($newShowFile)));
        File::put($newShowFile, str_replace('%%crudNameSingular%%', $this->crudNameSingular, File::get($newShowFile)));
        File::put($newShowFile, str_replace('%%crudNameCap%%', $this->crudNameCap, File::get($newShowFile)));
        File::put($newShowFile, str_replace('%%modelName%%', $this->modelName, File::get($newShowFile)));
        File::put($newShowFile, str_replace('%%primaryKey%%', $this->primaryKey, File::get($newShowFile)));
        File::put($newShowFile, str_replace('%%viewName%%', $this->viewName, File::get($newShowFile)));
        File::put($newShowFile, str_replace('%%routeGroup%%', $this->routeGroup, File::get($newShowFile)));
    }

    /**
     * Form field wrapper.
     *
     * @param  string $item
     * @param  string $field
     *
     * @return void
     */
    protected function wrapField($item, $field)
    {
        $formGroup = File::get($this->viewDirectoryPath . 'form-fields/wrap-field.blade.stub');

        $labelText = "'" . ucwords(strtolower(str_replace('_', ' ', $item['name']))) . "'";

        if ($this->option('localize') == 'yes') {
            $labelText = 'trans(\'' . $this->crudName . '.' . $item['name'] . '\')';
        }

        return sprintf($formGroup, $item['name'], $labelText, $field);
    }

    /**
     * Form field generator.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createField($item)
    {
        switch ($this->typeLookup[$item['type']]) {
            case 'password':
                return $this->createPasswordField($item);
                break;
            case 'datetime-local':
            case 'time':
                return $this->createInputField($item);
                break;
            case 'radio':
                return $this->createRadioField($item);
                break;
            case 'select':
            case 'enum':
                return $this->createSelectField($item);
                break;
            default: // text
                return $this->createFormField($item);
        }
    }

    /**
     * Create a specific field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createFormField($item)
    {
        $required = ($item['required'] === true) ? ", 'required' => 'required'" : "";

        $markup = File::get($this->viewDirectoryPath . 'form-fields/form-field.blade.stub');
        $markup = str_replace('%%required%%', $required, $markup);
        $markup = str_replace('%%fieldType%%', $this->typeLookup[$item['type']], $markup);
        $markup = str_replace('%%itemName%%', $item['name'], $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a password field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createPasswordField($item)
    {
        $required = ($item['required'] === true) ? ", 'required' => 'required'" : "";

        $markup = File::get($this->viewDirectoryPath . 'form-fields/password-field.blade.stub');
        $markup = str_replace('%%required%%', $required, $markup);
        $markup = str_replace('%%itemName%%', $item['name'], $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a generic input field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createInputField($item)
    {
        $required = ($item['required'] === true) ? ", 'required' => 'required'" : "";

        $markup = File::get($this->viewDirectoryPath . 'form-fields/input-field.blade.stub');
        $markup = str_replace('%%required%%', $required, $markup);
        $markup = str_replace('%%fieldType%%', $this->typeLookup[$item['type']], $markup);
        $markup = str_replace('%%itemName%%', $item['name'], $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }

    /**
     * Create a yes/no radio button group using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createRadioField($item)
    {
        $markup = File::get($this->viewDirectoryPath . 'form-fields/radio-field.blade.stub');

        return $this->wrapField($item, sprintf($markup, $item['name']));
    }

    /**
     * Create a select field using the form helper.
     *
     * @param  array $item
     *
     * @return string
     */
    protected function createSelectField($item)
    {
        $required = ($item['required'] === true) ? ", 'required' => 'required'" : "";

        $markup = File::get($this->viewDirectoryPath . 'form-fields/select-field.blade.stub');
        $markup = str_replace('%%required%%', $required, $markup);
        $markup = str_replace('%%options%%', $item['options'], $markup);
        $markup = str_replace('%%itemName%%', $item['name'], $markup);

        return $this->wrapField(
            $item,
            $markup
        );
    }
}
