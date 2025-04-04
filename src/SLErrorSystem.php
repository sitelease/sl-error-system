<?php

namespace Sitelease\ErrorSystem;

use ErrorException;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\View\ViewableData;

/**
 * This class is the main error class for sitelease's error system.
 *
 *
 * @author Benjamin Blake (sitelease.ca)
 * @package sl-error-system
 */
class SLErrorSystem extends ViewableData
{
    /**
     * Used to construct the server error page url.
     *
     * @author Benjamin Blake (sitelease.ca)
     * @var string
     * @config
     */
    private static $errorPageURLSegment = "server-error/";

    /**
     * The Email address you would like notified when an error occurs on the site
     *
     * @author Benjamin Blake (sitelease.ca)
     * @var string
     * @config
     */
    private static $mailTo = "Administrator <error_notification@sitelease.ca>";

    /**
     * Used to create the subject that will be displayed on error email notifications for this site.
     *
     * NOTE: The subject format is $subjectPrefix . $siteurl . $subjectSuffix
     *
     * @author Benjamin Blake (sitelease.ca)
     * @var string
     * @config
     */
    private static $mailSubjectPrefix = "Sitelease Error Notification for ";

    /**
     * Used to create the subject that will be displayed on error email notifications for this site
     *
     * NOTE: The subject format is $subjectPrefix . $siteurl . $subjectSuffix
     *
     * @author Benjamin Blake (sitelease.ca)
     * @var string
     * @config
     */
    private static $mailSubjectSuffix = "";

    /**
     * A custom error handler that converts all php errors into exceptions that
     * can be caught using a try/catch.
     * This is useful (and in most cases needed) for database transactions, as
     * you do not want any errors that are not considered exceptions to
     * cause data mismatching in your database.
     *
     * NOTE: You need to make sure to reset the error handler back to
     * silverstripes when you are finished. (Use restore_error_handler();)
     *
     * @author Benjamin Blake (sitelease.ca)
     * @param  [type] $errno      [description]
     * @param  [type] $errstr     [description]
     * @param  [type] $errfile    [description]
     * @param  [type] $errline    [description]
     * @param  array  $errcontext [description]
     * @return [type]             [description]
     */
    public static function errorsAsExceptionsErrorHandler($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Logs an error to the system logger and then notifies an admin via email
     *
     * @author Benjamin Blake (sitelease.ca)
     * @param  string $classAndMethodName The name of the file and method that triggered the error. Use __METHOD__.
     * @param  string $message An extra message that will be presented with the official message. Useful for describing what happened.
     * @param  string $error The official error or exception message (optional)
     * @return boolean Returns true if mail successfully accepted for delivery. Otherwise returns false
     */
    public static function sendErrorNotification($classAndMethodName, $message, $error = null)
    {
        // Build message from parameters
        $mailMessage = $classAndMethodName;
        $mailMessage .= " - " . $message;
        if (!empty($error)) {
            $mailMessage .= "<br  />Error: " . $error;
        }
        // Log the error to the system's error log
        error_log($mailMessage);
        // Notify an admin via email
        $mailTo = self::config()->mailTo;
        $mailSubject = self::config()->mailSubjectPrefix . Director::absoluteBaseURL() . self::config()->mailSubjectSuffix;
        // Try to deliver the email
        $delivery = mail($mailTo, $mailSubject, $mailMessage);
        // Return true if mail successfully accepted for delivery
        if ($delivery) {
            return true;
        } else {
            // Otherwise returns false
            return false;
        }
    }

    /**
     * Logs and error and notifies an admin using self::sendErrorNotification()
     * Then redirects to the 500 server error page
     *
     * @param  [type] $controller         The controller that will be used for the redirect
     * @param  string $classAndMethodName The name of the file and method that triggered the error. Use __METHOD__.
     * @param  string $message An extra message that will be presented with the official message. Useful for describing what happened.
     * @param  string $error The official error or exception message (optional)
     * @return [type] Returns a redirect to the server error page
     */
    public static function throw500Error($controller, $classAndMethodName, $message, $error = null)
    {
        $absoluteSiteURL = Director::absoluteBaseURL();
        if (empty($devMessage)) {
            $devMessage = "A 500 error occurred on " . $absoluteSiteURL;
        }
        self::sendErrorNotification($classAndMethodName, $message, $error);
        return $controller->redirect(Controller::join_links(
            $absoluteSiteURL,
            self::config()->errorPageURLSegment
        ));
    }
}
