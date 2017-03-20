<?php

namespace Abrachist\Webadmin\Controllers;

use App\Http\Controllers\Controller;
use Artisan;
use File;
use Illuminate\Http\Request;
use Response;
use Session;
use View;

class GenerateTransactionController extends Controller
{
    /**
     * Display generator.
     *
     * @return Response
     */
    public function getTransaction()
    {
        
        $path = app_path();

        $out = $this->getModels($path);

        return view('generator-transaction',compact('out'));
    }

    private function getModels($path) {
        $out = [];
        $results = scandir($path);
        foreach ($results as $result) {
            if ($result === '.' or $result === '..') continue;
            $filename = $path . '/' . $result;
            
            if (! is_dir($filename)){
                $split = explode('/',substr($filename,0,-4));
                $out[] = array_last($split);
            }
        }

        return $out;
    }

    /**
     * Process generator.
     *
     * @return Response
     */
    public function postTransaction(Request $request)
    {
        $commandArg = [];
        $commandArg['name'] = $request->crud_name;

        //get parent table request values
        if ($request->has('fields')) {
            $fieldsArray = [];
            $validationsArray = [];
            $x = 0;
            foreach ($request->fields as $field) {
                if ($request->fields_required[$x] == 1) {
                    $validationsArray[] = $field;
                }

                $fieldsArray[] = $field . '#' . $request->fields_type[$x];

                $x++;
            }

            $commandArg['--fields'] = implode(";", $fieldsArray);
        }

        //get child table request values
        if ($request->has('childfields')) {
            $fieldsArray2 = [];
            $validationsArray2 = [];
            $x = 0;
            foreach ($request->childfields as $field) {
                if ($request->childfields_required[$x] == 1) {
                    $validationsArray2[] = $field;
                }

                if ($request->has('childfields_foreignkey')) {
                    $foreignArray2 = $request->childfields_foreignkey[$x];
                }

                $fieldsArray2[] = $field . '#' . $request->childfields_type[$x] . '#' . $foreignArray2;

                $x++;
            }

            $commandArg['--fields_detail'] = implode(";", $fieldsArray2);
        }

        if (!empty($validationsArray)) {
            $commandArg['--validations'] = implode("#required;", $validationsArray) . "#required";
        }

        if (!empty($validationsArray2)) {
            $commandArg['--validations_detail'] = implode("#required;", $validationsArray2) . "#required";
        }

        if ($request->has('route')) {
            $commandArg['--route'] = $request->route;
        }

        if ($request->has('view_path')) {
            $commandArg['--view-path'] = $request->view_path;
        }

        if ($request->has('controller_namespace')) {
            $commandArg['--controller-namespace'] = $request->controller_namespace;
        }

        if ($request->has('model_namespace')) {
            $commandArg['--model-namespace'] = $request->model_namespace;
        }

        if ($request->has('route_group')) {
            $commandArg['--route-group'] = $request->route_group;
        }

        try {
            Artisan::call('cyom:generate-transaction', $commandArg);

            $name = camel_case($commandArg['name']);
            $routeName = ($commandArg['--route-group']) ? $commandArg['--route-group'] . '/' . snake_case($name, '-') : snake_case($name, '-');

            \DB::table('module')->insert([
                'name' => $name,
                'section' => 'modules',
                'url' => $routeName
            ]);

            Artisan::call('migrate');
        } catch (\Exception $e) {
            return Response::make($e->getMessage(), 500);
        }

        Session::flash('flash_message', 'Your CRUD has been generated. See on the menu.');

        return redirect('generator/transaction');
    }
}
