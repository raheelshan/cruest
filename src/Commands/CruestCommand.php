<?php

namespace Raheelshan\Cruest\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;

class CruestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:cruest {name : The resource name} {attributes?} {--cache} {--api} {--permission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model, controller, for requests and add routes';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $files;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var array The data types that can be created in a migration.
     */
    private $dataTypes = [
        'string', 'integer', 'boolean', 'bigIncrements', 'bigInteger',
        'binary', 'boolean', 'char', 'date', 'dateTime', 'float', 'increments',
        'json', 'jsonb', 'longText', 'mediumInteger', 'mediumText', 'nullableTimestamps',
        'smallInteger', 'tinyInteger', 'softDeletes', 'text', 'time', 'timestamp',
        'timestamps', 'rememberToken',
    ];

    private $fakerMethods = [
        'string' => ['method' => 'words', 'parameters' => '2, true'],
        'integer' => ['method' => 'randomNumber', 'parameters' => ''],
    ];

    /**
     * @var array $columnProperties Properties that can be applied to a table column.
     */
    private $columnProperties = [
        'unsigned', 'index', 'nullable'
    ];

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = trim($this->argument('name'));

        $this->createModel($name);

        $options = $this->options();

        $cache = isset($options['cache']) ? (bool)$options['cache'] : false;

        $api_required = isset($options['api']) ? (bool)$options['api'] : false;

        $this->createModel($name,$cache);

        $this->createRequests($name,$api_required);

        $this->createController($name);

        $this->addRoutes($name);
    }

    private function addRoutes($name)
    {
        $modelName = $this->modelName($name);
        $models = $this->modelName(Str::plural($modelName));

        $stub = $this->files->get(__DIR__ . '/../Stubs/routes.stub');
        $stub = $this->replaceClassName($modelName, $stub);
        $stub = $this->replacePermissionsNames($models, $stub);

        $this->files->append(base_path('routes/web.php'),$stub);        

        //$this->info('Routes added in web.php');
        //echo ('Routes added in web.php');                 

        $this->files->append(base_path('routes/api.php'),$stub);        

        //$this->info('Routes added in web.php');
        //echo ('Routes added in api.php');   
    }

    private function createController($name)
    {
        $modelName = $this->modelName($name);
        $models = $this->modelName(Str::plural($modelName));

        $stub = $this->files->get(__DIR__ . '/../Stubs/controller.stub');
        $stub = $this->replaceClassName($modelName, $stub);
        $stub = $this->replaceClassNames($models, $stub);
        $stub = $this->replacePermissionsNames($models, $stub);
        $stub = $this->replacePermissionName($modelName, $stub);        

        $filename = $modelName.'Controller.php';

        $path = app_path().'/Http/Controllers/'.$filename;

        if (!$this->files->exists($path)) 
        {
            $this->files->put($path, $stub);
            //$this->info($filename.' Request created');
            //echo ($filename.' created');                 
        }
    }

    private function createRequests($name,$api_required=false)
    {
        $modelName = $this->modelName($name);
        $models = $this->modelName(Str::plural($modelName));

        $path = app_path().'/Http/Requests';

        if(!$this->files->isDirectory($path)){
            $this->files->makeDirectory($path, 0777, true, true);
        }

        if($api_required){
            
            $stub = $this->files->get(__DIR__ . '/../Stubs/baserequest.stub');
            $filename = $path.'/BaseRequest.php';

            if (!$this->files->exists(app_path($filename))) {
                $this->files->put($filename, $stub);
                //$this->info('Base Request created');
                //echo ('Base Request created');                 
            }            

            $path = app_path().'/Core';

            if(!$this->files->isDirectory($path)){
                $this->files->makeDirectory($path, 0777, true, true);
            }

            $stub = $this->files->get(__DIR__ . '/../Stubs/response.stub');
            $filename = $path.'/Response.php';

            if (!$this->files->exists(app_path($filename))) {
                $this->files->put($filename, $stub);
                //$this->info('Response Class created');
                //echo ('Response Class created');                 
            } 
        }

        $path = app_path().'/Http/Requests/'.$modelName;
        
        if(!$this->files->isDirectory($path)){
            $this->files->makeDirectory($path, 0777, true, true);
        }

        $stub = $this->files->get(__DIR__ . '/../Stubs/getallmodelsrequest.stub');

        $stub = $this->replaceClassName($modelName, $stub);
        $stub = $this->replaceClassNames($models, $stub);
        $stub = $this->replacePermissionsNames($models, $stub);

        $filename = 'GetAll'. $models .'Request.php';

        if (!$this->files->exists($path.'/'.$filename)) {
            $this->files->put($path.'/' . $filename, $stub);
            //$this->info($filename.' created');
            //echo ($filename.' created');                 
        }

        $stub = $this->files->get(__DIR__ . '/../Stubs/getmodelrequest.stub');

        $stub = $this->replaceClassName($modelName, $stub);
        $stub = $this->replaceClassNames($models, $stub);
        $stub = $this->replacePermissionName($modelName, $stub);

        $filename = 'Get'. $modelName .'Request.php';

        if (!$this->files->exists($path.'/'.$filename)) {
            $this->files->put($path.'/' . $filename, $stub);
            //$this->info($filename.' created');
            //echo ($filename.' created');                 
        }

        //////////////////////////////////////
        $stub = $this->files->get(__DIR__ . '/../Stubs/createmodelrequest.stub');

        $stub = $this->replaceClassName($modelName, $stub);
        $stub = $this->replaceClassNames($models, $stub);
        $stub = $this->replacePermissionName($modelName, $stub);        

        $filename = 'Create'. $modelName .'Request.php';

        if (!$this->files->exists($path.'/'.$filename)) {
            $this->files->put($path.'/' . $filename, $stub);
            //$this->info($filename.' created');
            //echo ($filename.' created');                 
        }
        ///////////////////////////////////////
        $stub = $this->files->get(__DIR__ . '/../Stubs/deletemodelrequest.stub');

        $stub = $this->replaceClassName($modelName, $stub);
        $stub = $this->replaceClassNames($models, $stub);
        $stub = $this->replacePermissionName($modelName, $stub);

        $filename = 'Delete'. $modelName .'Request.php';

        if (!$this->files->exists($path.'/'.$filename)) {
            $this->files->put($path.'/' . $filename, $stub);
            //$this->info($filename.' created');
            //echo ($filename.' created');                 
        }
        ///////////////////////////////////////
        $stub = $this->files->get(__DIR__ . '/../Stubs/updatemodelrequest.stub');

        $stub = $this->replaceClassName($modelName, $stub);
        $stub = $this->replaceClassNames($models, $stub);
        $stub = $this->replacePermissionName($modelName, $stub);

        $filename = 'Update'. $modelName .'Request.php';

        if (!$this->files->exists($path.'/'.$filename)) {
            $this->files->put($path.'/' . $filename, $stub);
            //$this->info($filename.' created');
            //echo ($filename.' created');                 
        }        

    }

    private function createModel($name,$cache=false)
    {
        $modelName = $this->modelName($name);

        $filename = 'Models/'.$modelName . '.php';

        if ($this->files->exists(app_path($filename))) {
            //$this->error('Model already exists!');
            //echo ('Model already exists!');
            return false;
        }

        $model = $this->buildModel($modelName,$cache);

        $this->files->put(app_path('/' . $filename), $model);

        //$this->info($modelName . ' Model created');
        //echo ($modelName . ' Model created');

        return true;
    }

    protected function buildModel($name,$cache=false)
    {
        //$stub = $this->files->get(__DIR__ . '/../Stubs/model.stub');

        $stub = null;

        if($cache){

            $path = app_path().'/Support/Database';
            
            if(!$this->files->isDirectory($path)){
                $this->files->makeDirectory($path, 0777, true, true);
            }

            $stub = $this->files->get(__DIR__ . '/../Stubs/cachequerybuilder.stub');
            $filename = 'Support/Database/CacheQueryBuilder.php';

            if (!$this->files->exists(app_path($filename))) {
                $this->files->put(app_path('/' . $filename), $stub);
                //$this->info('CacheQueryBuilder trait created');
                //echo ('CacheQueryBuilder trait created');                 
            }

            $stub = $this->files->get(__DIR__ . '/../Stubs/builder.stub');
            $filename = 'Support/Database/Builder.php';

            if (!$this->files->exists(app_path($filename))) {
                $this->files->put(app_path('/' . $filename), $stub);
                //$this->info('Builder Class created');
                //echo ('Builder Class created');                 
            }

            $stub = $this->files->get(__DIR__ . '/../Stubs/basemodel.stub');
            $filename = 'Support/BaseModel.php';
            
            if (!$this->files->exists(app_path($filename))) {
                $this->files->put(app_path('/' . $filename), $stub);
                //$this->info('Base Model created');
                //echo ('Base Model created');                
            }

            $stub = $this->files->get(__DIR__ . '/../Stubs/cachemodel.stub');
        }else{
            $stub = $this->files->get(__DIR__ . '/../Stubs/model.stub');
        }

        $stub = $this->replaceClassName($name, $stub);

        return $stub;
    } 
    
    private function replaceClassName($name, $stub)
    {
        return str_replace('NAME_PLACEHOLDER', $name, $stub);
    }

    private function replaceClassNames($name, $stub)
    {
        return str_replace('NAMES_PLACEHOLDER', $name, $stub);
    }   

    private function replacePermissionName($name, $stub)
    {
        return str_replace('MODEL', strtolower($name), $stub);
    }

    private function replacePermissionsNames($name, $stub)
    {
        return str_replace('MODELS', strtolower($name), $stub);
    }   

    private function modelName($name)
    {
        return ucfirst($name);
    }
}