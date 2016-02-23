<?php 
namespace Sham\LoadParameters;


/**
* Clase abstracta de las que extenderán las clases que retornarán multiples atributos 
* (varias reglas, mensajes, parametros), que se usaran según el método donde se utilicen
*/
abstract class MultiAttributes
{
	
	// Forzar la extensión de clase para definir este método
    abstract protected function multiParams();
    abstract protected function multiRules();
    abstract protected function multiMessages();
}