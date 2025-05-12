<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse; // Alias to avoid naming conflict

/**
 * Class Response
 *
 * Provides a fluent interface for creating standardized JSON API responses.
 *
 * Usage:
 * Response::status('success')->code(200)->result($data);
 * Response::status('failure')->code(404)->result(['error' => 'Resource not found']);
 * Response::status('failure')->result(['field' => ['Validation error']]); // Defaults code to 422
 */
class Response
{
    /**
     * The type of response ('success' or 'failure').
     * @var string|null
     */
    protected static ?string $responseType = null;

    /**
     * The intended HTTP status code for the response.
     * @var int|null
     */
    protected static ?int $httpStatusCode = null;

    /**
     * The format for the response ('json' or 'raw').
     * 'raw' format might need specific handling depending on use case.
     * @var string
     */
    protected static string $responseFormat = 'json';

    /**
     * Sets the desired response type (e.g., 'success', 'failure').
     *
     * @param string $status Typically 'success' or 'failure'.
     * @return static Allows chaining.
     */
    public static function status(string $status): static
    {
        // Basic validation, can be expanded
        if (in_array(strtolower($status), ['success', 'failure'])) {
            self::$responseType = strtolower($status);
        } else {
            // Handle invalid status type if needed, maybe throw exception or default
            self::$responseType = 'failure'; // Default to failure if invalid type given
        }
        return new static; // Return instance for chaining
    }

    /**
     * Sets the HTTP status code for the response.
     *
     * @param int $code The HTTP status code (e.g., 200, 404, 500).
     * @return static Allows chaining.
     */
    public static function code(int $code): static
    {
        self::$httpStatusCode = $code;
        return new static; // Return instance for chaining
    }

    /**
     * Sets the response format to 'raw'.
     * Note: Raw format bypasses the standard JSON structure.
     *
     * @return static Allows chaining.
     */
    public function raw(): static
    {
        self::$responseFormat = 'raw';
        return new static; // Return instance for chaining
    }

    /**
     * Builds and returns the final response based on the chained methods.
     *
     * @param mixed $dataOrErrors The data for a success response or errors for a failure response.
     * @return \Illuminate\Http\JsonResponse|mixed Depending on the format ('json' or 'raw').
     */
    public function result(mixed $dataOrErrors = null): mixed
    {
        $responseType = self::$responseType ?? 'success'; // Default to success if status() wasn't called
        $httpCode = self::$httpStatusCode; // Get the code set by ::code()

        // Determine default HTTP code if not explicitly set by ::code()
        if (is_null($httpCode)) {
            $httpCode = ($responseType === 'success') ? HttpResponse::HTTP_OK : HttpResponse::HTTP_UNPROCESSABLE_ENTITY;
        }

        // --- Raw Format Handling ---
        if (self::$responseFormat === 'raw') {
            self::reset(); // Reset state
            // For raw, simply return the data with the specified HTTP code
            // Note: This assumes the controller wants to handle content-type etc.
            // If you need more control over raw responses, expand this logic.
            return response($dataOrErrors, $httpCode);
        }

        // --- JSON Format Handling ---
        $responseBody = [];
        if ($responseType === 'success') {
            // Call the internal success method to get the body structure
            $responseBody = self::buildSuccessBody($dataOrErrors, $httpCode);
        } elseif ($responseType === 'failure') {
            // Call the internal failure method to get the body structure
            $responseBody = self::buildFailureBody($dataOrErrors, $httpCode);
        } else {
            // Fallback for unexpected responseType (shouldn't happen with validation in status())
            $responseBody = self::buildFailureBody(['error' => 'Invalid response type specified.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
            $httpCode = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        self::reset(); // Reset state for the next potential call

        return response()->json($responseBody, $httpCode);
    }

    /**
     * Builds the structured array for a success response body.
     *
     * @param mixed $data The data payload.
     * @param int $statusCode The HTTP status code being used (included in the body).
     * @return array The structured response body.
     */
    protected static function buildSuccessBody(mixed $data = [], int $statusCode = HttpResponse::HTTP_OK): array
    {
        return [
            'status' => $statusCode,
            'message' => 'success', // Or derive from status code / allow customization
            'data' => $data ?? [], // Ensure data is at least an empty array if null
            'errors' => [] // Explicitly empty errors for success
        ];
    }

    /**
     * Builds the structured array for a failure response body.
     *
     * @param mixed $errors The error payload. Can be a string, array, or validation errors.
     * @param int $statusCode The HTTP status code being used (included in the body).
     * @return array The structured response body.
     */
    protected static function buildFailureBody(mixed $errors = [], int $statusCode = HttpResponse::HTTP_UNPROCESSABLE_ENTITY): array
    {
        $errorMessages = [];

        if (is_string($errors)) {
            // If a simple string is passed, wrap it in a generic error structure
            $errorMessages = ['message' => [$errors]];
        } elseif (is_array($errors)) {
            // Assume it's already structured errors (like from validation) or a simple list
            $errorMessages = $errors;
        } elseif ($errors instanceof \Illuminate\Contracts\Support\MessageBag) {
            // Handle Laravel Validator MessageBag
            $errorMessages = $errors->toArray();
        } else {
            // Fallback for unknown error type
            $errorMessages = ['message' => ['An unexpected error format occurred.']];
        }


        return [
            'status' => $statusCode,
            'message' => 'failure', // Or derive from status code / allow customization
            'data' => (object)[], // Use empty object for data on failure
            'errors' => $errorMessages
        ];
    }

    /**
     * Resets the static properties to their defaults for the next response generation.
     */
    protected static function reset(): void
    {
        self::$responseType = null;
        self::$httpStatusCode = null;
        self::$responseFormat = 'json';
    }
}
