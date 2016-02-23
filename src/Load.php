<?php 

namespace Sham\LoadParameters;

use Validator;
use Illuminate\Http\Request;

/**
* Clase principal que hace cargar y validar todos los parametros
*/
class Load
{
	const ERROR_BOOLEAN = 1;
	const ERROR_EXCEPTION = 2;
	const ERROR_ARRAY = 3;

	protected $typeError = self::ERROR_BOOLEAN;
	protected $validator;
	protected $separator = ', ';

    protected $class;
    protected $inputs = [];

	protected $params   = [];
	protected $rules    = [];
	protected $messages = [];


	public function __construct($class = '')
	{
        if (empty($class) || !is_object($class)) {
            $this->class = new \stdClass();
        }else{
            $this->class = $class;
        }
	}

    protected function getValueType($type)
    {
        if(method_exists($this->class, $type)){
            $this->$type = $this->class->$type();
        }

        return $this->$type;
    }

    public function loadMultiAttributes($name)
    {
        if (!($this->class instanceof MultiAttributes)) {
            throw new \Exception("Debe ingresar una clase", 500);
        }

        if(method_exists($this->class, 'multiRules')){
            $array = $this->class->multiRules();
            $this->rules = isset($array[$name]) ? $array[$name] : [];
        }

        if(method_exists($this->class, 'multiParams')){
            $array = $this->class->multiParams();
            $this->params = isset($array[$name]) ? $array[$name] : [];
        }

        if(method_exists($this->class, 'multiMessages')){
            $array = $this->class->multiMessages();
            $this->messages = isset($array[$name]) ? $array[$name] : [];
        }

    }

	public function validate($request, $method = 'post')
	{
		if (!$request->isMethod($method))
			throw new \Exception("El método de los parámetros no es el correcto", 500);

        $params   = $this->getValueType('params');
        $rules    = $this->getValueType('rules');
        $messages = $this->getValueType('messages');

        if (empty($rules)) {
            throw new \Exception("No se encontraron reglas para validar", 500);
        }

        if (!empty($params)) {
            $inputs = $this->changeKey($request->all());
        } else {
            $inputs = $request->all();
        }

		$this->validator = Validator::make($inputs, $rules, $messages);

		if($this->validator->fails()){
			return false;
		}

        $this->inputs = $inputs;
		return true;
	}

    public function changeKey($original = [], $inverse = false)
    {
        $array = [];

        if ($inverse) {
            $oldkeys = array_keys($this->params);
            $newkeys = array_values($this->params);
        } else {
            $oldkeys = array_values($this->params);
            $newkeys = array_keys($this->params);
        }

        for ($i=0; $i < count($newkeys); $i++) { 
            if (isset($original[$oldkeys[$i]])) {
                $array[$newkeys[$i]] = $original[$oldkeys[$i]];
            }
        }

        return $array;
    }

	public function getErrors()
	{
		switch ($this->typeError) {
			case self::ERROR_BOOLEAN:
				return false;
				break;

			case self::ERROR_EXCEPTION:
                $errors = array_map(function($v){return implode($this->separator, $v);}, $this->validator->errors()->getMessages());
                if (!empty($this->params)) {
                    $errors = $this->changeKey($errors, true);
                }
				throw new \Exception(implode(". \n", $errors), 500);
				break;

			case self::ERROR_ARRAY:
				$errors = array_map(function($v){return implode($this->separator, $v);}, $this->validator->errors()->getMessages());
                if (!empty($this->params)) {
                    return $this->changeKey($errors, true);
                }
				return $errors;
				break;
			
			default:
				return false;
				break;
		}
	}

    /**
     * Gets the value of typeError.
     *
     * @return mixed
     */
    public function getTypeError()
    {
        return $this->typeError;
    }

    /**
     * Sets the value of typeError.
     *
     * @param mixed $typeError the type error
     *
     * @return self
     */
    public function setTypeError($typeError)
    {
        $this->typeError = $typeError;

        return $this;
    }

    /**
     * Gets the value of separator.
     *
     * @return mixed
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Sets the value of separator.
     *
     * @param mixed $separator the separator
     *
     * @return self
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Gets the value of params.
     *
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the value of params.
     *
     * @param mixed $params the params
     *
     * @return self
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Gets the value of rules.
     *
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Sets the value of rules.
     *
     * @param mixed $rules the rules
     *
     * @return self
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Sets the value of messages.
     *
     * @param mixed $messages the messages
     *
     * @return self
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Gets the value of messages.
     *
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Gets the value of inputs.
     *
     * @return mixed
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * Gets the value of validator.
     *
     * @return mixed
     */
    public function getValidator()
    {
        return $this->validator;
    }
}