<?php
/**
 * Example using ding mvc.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  global
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */

////////////////////////////////////////////////////////////////////////////////
// Mandatory stuff to bootstrap ding. (START)
////////////////////////////////////////////////////////////////////////////////
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            ini_get('include_path'),
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg'))
        )
    )
);
require_once 'Ding/Autoloader/Ding_Autoloader.php'; // Include ding autoloader.
Ding_Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\HttpSession\HttpSession;
use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Helpers\ErrorHandler\IErrorHandler;
use Ding\MVC\ModelAndView;
use Ding\MVC\Http\HttpFrontController;
////////////////////////////////////////////////////////////////////////////////
// Normal operation follows...
////////////////////////////////////////////////////////////////////////////////
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);
////////////////////////////////////////////////////////////////////////////////
class AnException extends \Exception
{
}

class MyController
{
    public function ExceptionException(array $arguments = array())
    {
        $modelAndView = new ModelAndView('exception');
        $modelAndView->add(array('exception' => $arguments['exception']));
        return $modelAndView;
    }

    public function formAction(array $arguments = array())
    {
        $session = HttpSession::getSession();
        $arguments['sessionStuff'] = $session->getAttribute('aSessionVariable');
        return $this->someAction($arguments);
    }

    public function redirectAction(array $arguments = array())
    {
        $modelAndView = new ModelAndView('some');
        $modelAndView->add(
            array(
            	'headers' => array(
                    'HTTP/1.1 302 Moved',
                    'Location: http://github.com/marcelog/Ding'
                )
            )
        );
        return $modelAndView;
    }

    public function someAction(array $arguments = array())
    {
        $session = HttpSession::getSession();
        $session->setAttribute('aSessionVariable', array('user' => 'aUser'));
        $modelAndView = new ModelAndView('some');
        $modelAndView->add(array(
        	'somestring' => 'Hello World',
            'arguments' => $arguments
        ));
        $modelAndView->add(
            array(
            	'headers' => array(
            		'Cache-Control: no-cache',
                    'Pragma: no-cache'
                )
            )
        );
        return $modelAndView;
    }

    public function someExceptionAction(array $arguments = array())
    {
        throw new AnException('Woooooow!');
    }
}

class MyErrorHandler implements IErrorHandler
{
    public function handleError(ErrorInfo $error)
    {
        echo "This is your custom error handler: " . print_r($error, true);
    }
}

$properties = array(
    'ding' => array(
    	'log4php.properties' => realpath('./log4php.properties'),
        'factory' => array(
            'drivers' => array(
                'signalhandler' => array(),
				'shutdown' => array(),
				'errorhandler' => array()
            ),
            'bdef' => array(
             	'xml' => array('filename' => realpath('./beans.xml')),
                'annotation' => array('scanDir' => array(realpath(__DIR__)))
            ),
        	'properties' => array(
                'baseUrl' => '/Some/Mapped/Path',
    	    	'viewPath' => './views',
    			'viewSuffix' => '.html',
    			'viewPrefix' => 'view.',
                'timezone' => 'America/Buenos_Aires'
            )
        ),
        'cache' => array(
            'proxy' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/proxy'),
            'bdef' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/bdef'),
            'beans' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/beans'),
        )
    )
);
HttpFrontController::configure($properties);
$frontController = new HttpFrontController();
$frontController->handle();
////////////////////////////////////////////////////////////////////////////////