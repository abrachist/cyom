<?php

namespace Abrachist\Webadmin\Commands\TransactionGenerator;

use Illuminate\Console\GeneratorCommand;

class GenerateTransactionController extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cyom:transaction-controller
                            {name : The name of the controler.}
                            {--crud-name= : The name of the Crud.}
                            {--model-name= : The name of the Model.}
                            {--model-namespace= : The namespace of the Model.}
                            {--view-path= : The name of the view path.}
                            {--fields= : Fields name for the form & migration.}
                            {--fields_detail= : Fields name for the form & migration child table.}
                            {--validations= : Validation details for the fields.}
                            {--route-group= : Prefix of the route group.}
                            {--pagination=25 : The amount of models per page for index pages.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('cyom.custom_template')
        ? config('cyom.path') . '/controller.stub'
        : __DIR__ . '/../../template/transaction/controller.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Controllers';
    }

    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $viewPath = $this->option('view-path') ? $this->option('view-path') . '.' : '';
        $crudName = strtolower($this->option('crud-name'));
        $crudNameSingular = str_singular($crudName);
        $modelName = $this->option('model-name');
        $modelNameDetail = $this->option('model-name').'Detail';
        $modelNamespace = $this->option('model-namespace');
        $routeGroup = ($this->option('route-group')) ? $this->option('route-group') . '/' : '';
        $perPage = intval($this->option('pagination'));
        $viewName = snake_case($this->option('crud-name'), '-');
        $fields = $this->option('fields');
        $fields_detail = $this->option('fields_detail');
        $validations = rtrim($this->option('validations'), ';');

        $validationRules = '';
        if (trim($validations) != '') {
            $validationRules = "\$this->validate(\$request, [";

            $rules = explode(';', $validations);
            foreach ($rules as $v) {
                if (trim($v) == '') {
                    continue;
                }

                // extract field name and args
                $parts = explode('#', $v);
                $fieldName = trim($parts[0]);
                $rules = trim($parts[1]);
                $validationRules .= "\n\t\t\t'$fieldName' => '$rules',";
            }

            $validationRules = substr($validationRules, 0, -1); // lose the last comma
            $validationRules .= "\n\t\t]);";
        }

        $snippet = <<<EOD
if (\$request->hasFile('{{fieldName}}')) {
    \$uploadPath = public_path('/uploads/');

    \$extension = \$request->file('{{fieldName}}')->getClientOriginalExtension();
    \$fileName = rand(11111, 99999) . '.' . \$extension;

    \$request->file('{{fieldName}}')->move(\$uploadPath, \$fileName);
    \$requestData['{{fieldName}}'] = \$fileName;
}
EOD;

        $fieldsArray = explode(';', $fields);
        $fieldsDetailArray = explode(';', $fields_detail);
        $fileSnippet = '';
        $whereSnippet = '';
        $createDetailSnippet = '';
        $firstFieldsDetail = '';

        if ($fields) {
            $x = 0;
            foreach ($fieldsArray as $index => $item) {
                $itemArray = explode('#', $item);

                if (trim($itemArray[1]) == 'file') {
                    $fileSnippet .= "\n\n" . str_replace('{{fieldName}}', trim($itemArray[0]), $snippet) . "\n";
                }

                $fieldName = trim($itemArray[0]);

                $whereSnippet .= ($index == 0) ? "where('$fieldName', 'LIKE', \"%\$keyword%\")" . "\n\t\t\t\t" : "->orWhere('$fieldName', 'LIKE', \"%\$keyword%\")" . "\n\t\t\t\t";
            }
        }

        if ($fields_detail) {
            $x = 0;
            $createDetailSnippet .= '$'. $crudNameSingular . '_detail = new ' . $modelNameDetail . ';' ."\n\t\t\t";

            foreach ($fieldsDetailArray as $index => $item) {
                $itemArray = explode('#', $item);

                $fieldName = trim($itemArray[0]);

                if($x > 0){
                    $createDetailSnippet .= '$'. $crudNameSingular.'_detail->'.$fieldName. " = ". "\$requestData['".$fieldName."'][\$i];"."\n\t\t\t" ;
                }

                if($x==1){
                    $firstFieldsDetail = trim($itemArray[0]);
                }

                $x++;
            }

            $createDetailSnippet .= '$'. $crudNameSingular.'_detail->'. $crudNameSingular .'_id = $' . $crudNameSingular . '->id;'."\n\t\t\t";
            $createDetailSnippet .= '$'. $crudNameSingular.'_detail->save();'."\n";
        }

        return $this->replaceNamespace($stub, $name)
            ->replaceViewPath($stub, $viewPath)
            ->replaceViewName($stub, $viewName)
            ->replaceCrudName($stub, $crudName)
            ->replaceCrudNameSingular($stub, $crudNameSingular)
            ->replaceModelName($stub, $modelName)
            ->replaceModelNameDetail($stub, $modelNameDetail)
            ->replaceModelNamespace($stub, $modelNamespace)
            ->replaceRouteGroup($stub, $routeGroup)
            ->replaceValidationRules($stub, $validationRules)
            ->replacePaginationNumber($stub, $perPage)
            ->replaceFileSnippet($stub, $fileSnippet)
            ->replaceWhereSnippet($stub, $whereSnippet)
            ->replaceDetailSnippet($stub, $createDetailSnippet)
            ->replaceFirstField($stub, $firstFieldsDetail)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the viewName fo the given stub.
     *
     * @param string $stub
     * @param string $viewName
     *
     * @return $this
     */
    protected function replaceViewName(&$stub, $viewName)
    {
        $stub = str_replace(
            '{{viewName}}', $viewName, $stub
        );

        return $this;
    }

    /**
     * Replace the viewPath for the given stub.
     *
     * @param  string  $stub
     * @param  string  $viewPath
     *
     * @return $this
     */
    protected function replaceViewPath(&$stub, $viewPath)
    {
        $stub = str_replace(
            '{{viewPath}}', $viewPath, $stub
        );

        return $this;
    }

    /**
     * Replace the crudName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $crudName
     *
     * @return $this
     */
    protected function replaceCrudName(&$stub, $crudName)
    {
        $stub = str_replace(
            '{{crudName}}', $crudName, $stub
        );

        return $this;
    }

    /**
     * Replace the crudNameSingular for the given stub.
     *
     * @param  string  $stub
     * @param  string  $crudNameSingular
     *
     * @return $this
     */
    protected function replaceCrudNameSingular(&$stub, $crudNameSingular)
    {
        $stub = str_replace(
            '{{crudNameSingular}}', $crudNameSingular, $stub
        );

        return $this;
    }

    /**
     * Replace the modelName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelName
     *
     * @return $this
     */
    protected function replaceModelName(&$stub, $modelName)
    {
        $stub = str_replace(
            '{{modelName}}', $modelName, $stub
        );

        return $this;
    }

    /**
     * Replace the modelNameDetail for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelNameDetail
     *
     * @return $this
     */
    protected function replaceModelNameDetail(&$stub, $modelNameDetail)
    {
        $stub = str_replace(
            '{{modelNameDetail}}', $modelNameDetail, $stub
        );

        return $this;
    }

    /**
     * Replace the modelName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $modelName
     *
     * @return $this
     */
    protected function replaceModelNamespace(&$stub, $modelNamespace)
    {
        $stub = str_replace(
            '{{modelNamespace}}', $modelNamespace, $stub
        );

        return $this;
    }

    /**
     * Replace the routeGroup for the given stub.
     *
     * @param  string  $stub
     * @param  string  $routeGroup
     *
     * @return $this
     */
    protected function replaceRouteGroup(&$stub, $routeGroup)
    {
        $stub = str_replace(
            '{{routeGroup}}', $routeGroup, $stub
        );

        return $this;
    }

    /**
     * Replace the validationRules for the given stub.
     *
     * @param  string  $stub
     * @param  string  $validationRules
     *
     * @return $this
     */
    protected function replaceValidationRules(&$stub, $validationRules)
    {
        $stub = str_replace(
            '{{validationRules}}', $validationRules, $stub
        );

        return $this;
    }

    /**
     * Replace the pagination placeholder for the given stub
     *
     * @param $stub
     * @param $perPage
     *
     * @return $this
     */
    protected function replacePaginationNumber(&$stub, $perPage)
    {
        $stub = str_replace(
            '{{pagination}}', $perPage, $stub
        );

        return $this;
    }

    /**
     * Replace the file snippet for the given stub
     *
     * @param $stub
     * @param $fileSnippet
     *
     * @return $this
     */
    protected function replaceFileSnippet(&$stub, $fileSnippet)
    {
        $stub = str_replace(
            '{{fileSnippet}}', $fileSnippet, $stub
        );

        return $this;
    }

    /**
     * Replace the where snippet for the given stub
     *
     * @param $stub
     * @param $whereSnippet
     *
     * @return $this
     */
    protected function replaceWhereSnippet(&$stub, $whereSnippet)
    {
        $stub = str_replace(
            '{{whereSnippet}}', $whereSnippet, $stub
        );

        return $this;
    }

    /**
     * Replace the detail snippet for the given stub
     *
     * @param $stub
     * @param $whereSnippet
     *
     * @return $this
     */
    protected function replaceDetailSnippet(&$stub, $detailSnippet)
    {
        $stub = str_replace(
            '{{detailSnippet}}', $detailSnippet, $stub
        );

        return $this;
    }

    /**
     * Replace the first detail fields for the given stub
     *
     * @param $stub
     * @param $whereSnippet
     *
     * @return $this
     */
    protected function replaceFirstField(&$stub, $firstFieldsDetail)
    {
        $stub = str_replace(
            '{{firstFieldsDetail}}', $firstFieldsDetail, $stub
        );

        return $this;
    }
}
