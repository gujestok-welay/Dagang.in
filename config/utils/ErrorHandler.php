<?php
/**
 * Error Handler Class
 * Centralized error handling with logging and user-friendly messages
 */

class ErrorHandler
{
    private static $errors = [];
    private static $log_file = null;

    /**
     * Initialize error handler with log file path
     *
     * @param string $log_path Path to log file
     */
    public static function init($log_path = null)
    {
        if ($log_path) {
            self::$log_file = $log_path;
            // Ensure log directory exists
            $log_dir = dirname($log_path);
            if (!is_dir($log_dir)) {
                @mkdir($log_dir, 0755, true);
            }
        }

        // Set custom error handler
        set_error_handler([self::class, 'handleError']);

        // Set custom exception handler
        set_exception_handler([self::class, 'handleException']);

        // Handle fatal errors
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    /**
     * Handle PHP errors
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        $error_types = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE_ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE_ERROR',
            E_CORE_WARNING => 'CORE_WARNING',
            E_COMPILE_ERROR => 'COMPILE_ERROR',
            E_COMPILE_WARNING => 'COMPILE_WARNING',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER_DEPRECATED',
        ];

        $error_type = $error_types[$errno] ?? 'UNKNOWN';
        $error_message = "[{$error_type}] {$errstr} in {$errfile} on line {$errline}";

        self::log($error_message);
        self::addError($error_message);

        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Handle exceptions
     *
     * @param Throwable $exception
     */
    public static function handleException($exception)
    {
        $error_message = "Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();

        self::log($error_message);
        self::addError($error_message);

        // Display user-friendly error
        self::displayError("Terjadi kesalahan. Silahkan coba lagi atau hubungi administrator.");
    }

    /**
     * Handle fatal errors
     */
    public static function handleFatalError()
    {
        $error = error_get_last();

        if ($error !== null) {
            $error_types = [
                E_ERROR => 'ERROR',
                E_PARSE => 'PARSE_ERROR',
                E_CORE_ERROR => 'CORE_ERROR',
                E_COMPILE_ERROR => 'COMPILE_ERROR',
            ];

            if (in_array($error['type'], array_keys($error_types))) {
                $error_type = $error_types[$error['type']];
                $error_message = "[{$error_type}] {$error['message']} in {$error['file']} on line {$error['line']}";

                self::log($error_message);
                self::displayError("Terjadi kesalahan fatal. Silahkan hubungi administrator.");
            }
        }
    }

    /**
     * Log error to file and/or array
     *
     * @param string $message
     */
    private static function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] {$message}" . PHP_EOL;

        // Log to file if configured
        if (self::$log_file && is_writable(dirname(self::$log_file))) {
            @file_put_contents(self::$log_file, $log_message, FILE_APPEND);
        }

        // Keep in memory
        self::addError($log_message);
    }

    /**
     * Add error to array
     *
     * @param string $error
     */
    private static function addError($error)
    {
        self::$errors[] = $error;
    }

    /**
     * Get all logged errors
     *
     * @return array
     */
    public static function getErrors()
    {
        return self::$errors;
    }

    /**
     * Clear errors
     */
    public static function clearErrors()
    {
        self::$errors = [];
    }

    /**
     * Display error to user
     *
     * @param string $message User-friendly message
     */
    public static function displayError($message)
    {
        // Set response code
        http_response_code(500);

        // Display error message
        echo '<div style="margin: 20px; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24; font-family: Arial, sans-serif;">';
        echo '<h3>Error</h3>';
        echo '<p>' . htmlspecialchars($message) . '</p>';
        echo '</div>';
    }

    /**
     * Safe database query execution
     *
     * @param mysqli_stmt $stmt
     * @param string $bind_types
     * @param array $params
     * @return bool
     */
    public static function executeQuery($stmt, $bind_types = '', $params = [])
    {
        try {
            if ($bind_types && !empty($params)) {
                $stmt->bind_param($bind_types, ...$params);
            }

            if (!$stmt->execute()) {
                self::log("Database error: " . $stmt->error);
                return false;
            }

            return true;
        } catch (Exception $e) {
            self::log("Query execution error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Safely get GET/POST parameters
     *
     * @param string $key Parameter key
     * @param string $method 'GET' or 'POST'
     * @param string $type 'int', 'string', 'float'
     * @param mixed $default Default value
     * @return mixed
     */
    public static function getParam($key, $method = 'GET', $type = 'string', $default = null)
    {
        $source = $method === 'POST' ? $_POST : $_GET;

        if (!isset($source[$key])) {
            return $default;
        }

        $value = $source[$key];

        switch ($type) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'string':
            default:
                return trim($value);
        }
    }

    /**
     * Validate email
     *
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     *
     * @param string $url
     * @return bool
     */
    public static function validateUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Sanitize input
     *
     * @param string $input
     * @return string
     */
    public static function sanitize($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate required fields
     *
     * @param array $fields
     * @param array $data
     * @return array Array of missing fields
     */
    public static function validateRequired($fields, $data)
    {
        $missing = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing[] = $field;
            }
        }

        return $missing;
    }
}
?>