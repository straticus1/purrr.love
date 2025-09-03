"""
🐱 Purrr.love Python SDK - Custom Exceptions
Custom exception classes for the Purrr.love SDK
"""


class PurrrLoveError(Exception):
    """Base exception for all Purrr.love SDK errors"""
    
    def __init__(self, message: str, code: int = None, details: dict = None):
        self.message = message
        self.code = code
        self.details = details or {}
        super().__init__(self.message)
    
    def __str__(self):
        if self.code:
            return f"[{self.code}] {self.message}"
        return self.message


class AuthenticationError(PurrrLoveError):
    """Raised when authentication fails"""
    
    def __init__(self, message: str = "Authentication failed", details: dict = None):
        super().__init__(message, code=401, details=details)


class RateLimitError(PurrrLoveError):
    """Raised when rate limit is exceeded"""
    
    def __init__(self, message: str = "Rate limit exceeded", retry_after: int = None, details: dict = None):
        self.retry_after = retry_after
        if retry_after:
            message = f"{message}. Retry after {retry_after} seconds"
        super().__init__(message, code=429, details=details)


class ValidationError(PurrrLoveError):
    """Raised when input validation fails"""
    
    def __init__(self, message: str = "Validation failed", field: str = None, details: dict = None):
        self.field = field
        if field:
            message = f"Validation failed for field '{field}': {message}"
        super().__init__(message, code=400, details=details)


class NotFoundError(PurrrLoveError):
    """Raised when a resource is not found"""
    
    def __init__(self, message: str = "Resource not found", resource_type: str = None, resource_id: str = None, details: dict = None):
        self.resource_type = resource_type
        self.resource_id = resource_id
        if resource_type and resource_id:
            message = f"{resource_type} with ID '{resource_id}' not found"
        super().__init__(message, code=404, details=details)


class PermissionError(PurrrLoveError):
    """Raised when user lacks permission for an action"""
    
    def __init__(self, message: str = "Permission denied", action: str = None, resource: str = None, details: dict = None):
        self.action = action
        self.resource = resource
        if action and resource:
            message = f"Permission denied: cannot {action} on {resource}"
        super().__init__(message, code=403, details=details)


class ConflictError(PurrrLoveError):
    """Raised when there's a conflict with the current state"""
    
    def __init__(self, message: str = "Conflict with current state", conflict_type: str = None, details: dict = None):
        self.conflict_type = conflict_type
        if conflict_type:
            message = f"Conflict ({conflict_type}): {message}"
        super().__init__(message, code=409, details=details)


class ServerError(PurrrLoveError):
    """Raised when the server encounters an error"""
    
    def __init__(self, message: str = "Server error occurred", details: dict = None):
        super().__init__(message, code=500, details=details)


class NetworkError(PurrrLoveError):
    """Raised when network-related errors occur"""
    
    def __init__(self, message: str = "Network error occurred", original_error: Exception = None, details: dict = None):
        self.original_error = original_error
        if original_error:
            message = f"{message}: {str(original_error)}"
        super().__init__(message, code=None, details=details)


class TimeoutError(PurrrLoveError):
    """Raised when a request times out"""
    
    def __init__(self, message: str = "Request timed out", timeout_seconds: int = None, details: dict = None):
        self.timeout_seconds = timeout_seconds
        if timeout_seconds:
            message = f"Request timed out after {timeout_seconds} seconds"
        super().__init__(message, code=408, details=details)


class QuotaExceededError(PurrrLoveError):
    """Raised when API quota is exceeded"""
    
    def __init__(self, message: str = "API quota exceeded", quota_type: str = None, limit: int = None, details: dict = None):
        self.quota_type = quota_type
        self.limit = limit
        if quota_type and limit:
            message = f"{quota_type} quota exceeded (limit: {limit})"
        super().__init__(message, code=429, details=details)


class MaintenanceError(PurrrLoveError):
    """Raised when the service is under maintenance"""
    
    def __init__(self, message: str = "Service under maintenance", estimated_duration: str = None, details: dict = None):
        self.estimated_duration = estimated_duration
        if estimated_duration:
            message = f"{message}. Estimated duration: {estimated_duration}"
        super().__init__(message, code=503, details=details)


class InvalidResponseError(PurrrLoveError):
    """Raised when the API returns an invalid response"""
    
    def __init__(self, message: str = "Invalid response from server", response_data: dict = None, details: dict = None):
        self.response_data = response_data
        if response_data:
            message = f"{message}. Response: {response_data}"
        super().__init__(message, code=None, details=details)


class ConfigurationError(PurrrLoveError):
    """Raised when there's a configuration issue"""
    
    def __init__(self, message: str = "Configuration error", config_key: str = None, details: dict = None):
        self.config_key = config_key
        if config_key:
            message = f"Configuration error for '{config_key}': {message}"
        super().__init__(message, code=None, details=details)


# Utility function to convert HTTP status codes to appropriate exceptions
def create_exception_from_status_code(status_code: int, message: str = None, details: dict = None) -> PurrrLoveError:
    """
    Create an appropriate exception based on HTTP status code
    
    Args:
        status_code: HTTP status code
        message: Error message
        details: Additional error details
        
    Returns:
        Appropriate exception instance
    """
    if not message:
        message = f"HTTP {status_code} error"
    
    if status_code == 400:
        return ValidationError(message, details=details)
    elif status_code == 401:
        return AuthenticationError(message, details=details)
    elif status_code == 403:
        return PermissionError(message, details=details)
    elif status_code == 404:
        return NotFoundError(message, details=details)
    elif status_code == 408:
        return TimeoutError(message, details=details)
    elif status_code == 409:
        return ConflictError(message, details=details)
    elif status_code == 429:
        return RateLimitError(message, details=details)
    elif status_code == 503:
        return MaintenanceError(message, details=details)
    elif status_code >= 500:
        return ServerError(message, details=details)
    else:
        return PurrrLoveError(message, code=status_code, details=details)
