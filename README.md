# Load Parameters Laravel 5 y Lumen
Carga los parámetros enviados por PUT, POST o GET; y los valida según las reglas asignadas en el proyecto.

Esta librería lo primero que hace es cargar los datos enviados a la url, después les asigna un nuevo nombre a cada variable (en caso se haya especificado) y finalmente se valida los datos con el [Validador de Laravel](https://laravel.com/docs/master/validation). Si todo los datos son correctos, retorna los inputs enviados en un array, sino retornará los errores encontrados.

La librería maneja tres aspectos importantes:
  - Rules: que es un array de reglas que se usará para validar los campos, estan reglas también pueden ser las [reglas personalizadas](https://laravel.com/docs/master/validation#custom-validation-rules)
  - Messages: Son los mensajes que se mostrarán en caso de error.
  - Params: Esta es una variable nueva para la librería, ya que con ella podemos especificar el nombre de una variable para ser enviada por POST GET o PUT, pero nosotros la utilizaremos con otro nombre, es decir, tenemos nuestro paramétro `usuario` enviado por POST, sin embargo en nuestra tabla o nuestra aplicación necesitamos que se llame `user`, es por eso que se usa esto, para cambiar de nombre a la variable, así el cliente cuando envíe los datos le saldrá un error con la variable `usuario`, pero nosotros la utilizaremos como `user`. Esto sería más usado en API REST ya que el nombre del dato enviado no siempre coincide con la varible que manejamos (en REST `nombre_usuario` en la aplicación `nombreUsuario`).

## Instalación
La instalación actual es sólo por composer
```sh
composer require sham/load-parameters-laravel dev-master
```

## Uso
Actualmente hay tres formas de uso de la librería:

### Método 1:
La primera forma de utilizar la librería es usando una clase con los métodos necesarios (rules, messages, params) puede ser una clase [Form Validator](https://laravel.com/docs/master/validation#form-request-validation) de Laravel

```php
...
use Sham\LoadParameters\Load;

class MyController extends Controller {

	public function myMethod(Request $request)
	{
		$load = new Load(new App\Http\Requests\MyFormRequest());

		if(!$load->validate($request, 'post')){
			$load->setTypeError(Load::ERROR_ARRAY);
			var_dump($load->getErrors());
		}else{
			extract($load->getInputs());
			echo "welcome $user";
		}
	}
...
```
La clase `MyFormRequest` debe tener por lo menos el método `rules()`; `params()` y `messages()` son opcionales.
```php
class MyFormRequest extends FormRequest {

	public function rules()
    {
    	return [
			'user' => 'required',
			'pass' => 'required|min:6',
    	];
    }
...
```
La forma de llamarlo sería:
```sh
curl -d "user=myuser" -d "pass=mipass"  "http://myurl.com/my-function"
```
y retornaría `welcome myuser`

### Método 2:
La siguiente forma es añadiendo un array con las reglas, mensajes y parámetros a usar:
```php
...
use Sham\LoadParameters\Load;

class MyController extends Controller {

	public function myMethod(Request $request)
	{
		$load = new Load();
		//obligatorio
		$load->setRules([
			'user' => 'required',
			'pass' => 'required|min:6',
		]);
		//opcional
		$load->setParams([
			//variable en tu proyecto => nombre de parametro de envio
			'user' => 'usuario',
			'pass' => 'contrasena',
		]);
        //opcional
		$load->setMessages([
			'user.required' => 'usuario es requerido', 
			'pass.required' => 'contrasena es requerido', 
			'pass.min' => 'Debe ingresar al menos 6 caracteres', 
		]);

		if(!$load->validate($request, 'post')){
			$load->setTypeError(Load::ERROR_ARRAY);
			var_dump($load->getErrors());
		}else{
			extract($load->getInputs());
			echo "welcome $user";
		}
	}
...
```
La forma de llamarlo sería:
```sh
curl -d "usuario=myuser" -d "contrasena=mipass"  "http://myurl.com/my-function"
```
y retornaría `welcome myuser`

### Método 3:
Si son varios los métodos en tu proyecto y no quieres llenarte de más lineas en tu controlador, puedes usar una clase en la cual tengas todas las reglas de validación por método, para ello primero debes crear una clase en donde creas conveniente y extender de `Sham\LoadParameters\MultiAttributes`:

```php
...
use Sham\LoadParameters\MultiAttributes;

class MyParameters extends MultiAttributes
{
    public function multiParams()
    {
    	return [
    		'myMethodFirst' => [
    			'user' => 'usuario',
    			'pass' => 'contrasena',
    		]
    	];
    }

    public function multiRules()
    {
    	return [
    		'myMethodFirst' => [
    			'user' => 'required',
    			'pass' => 'required|min:6',
    		],
    		'myMethodSecond' => [
    			'name' => 'required',
    		]
    	];
    }

    public function multiMessages()
    {
    	return [
    		'myMethodFirst' => [
    			'user.required' => 'usuario es requerido', 
				'pass.required' => 'contrasena es requerido', 
				'pass.min' => 'Debe ingresar al menos 6 caracteres', 
    		],
    		'myMethodSecond' => [
    			'name' => 'el nombre es required',
    		]
    	];
    }
...
```
Después en tu controlador:
```php
use Sham\LoadParameters\Load;

class MyController extends Controller {

	public function myMethod1(Request $request)
	{
		$load = new Load(new \path\to\MyParameters());
		$load->loadMultiAttributes('myMethod1');

		if(!$load->validate($request, 'post')){
			$load->setTypeError(Load::ERROR_ARRAY);
			var_dump($load->getErrors());
		}else{
			extract($load->getInputs());
			echo "welcome $user";
		}
	}
	
	public function myMethod2(Request $request)
	{
		$load = new Load(new \path\to\MyParameters());
		$load->loadMultiAttributes(__FUNCTION__);

		if(!$load->validate($request, 'post')){
			$load->setTypeError(Load::ERROR_ARRAY);
			var_dump($load->getErrors());
		}else{
			extract($load->getInputs());
			echo $user;
		}
	}
...
```
La forma de llamarlo sería al metodo 1:
```sh
curl -d "usuario=myuser" -d "contrasena=mipass"  "http://myurl.com/my-function-1"
```
y al metodo 2:
```sh
curl -d "name=my name"  "http://myurl.com/my-function-2"
```

