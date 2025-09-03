"""
🐱 Purrr.love Python SDK
Official Python client library for the Purrr.love cat gaming platform
"""

__version__ = "2.0.0"
__author__ = "Purrr.love Team"
__email__ = "dev@purrr.love"

from .client import PurrrLoveClient
from .models import Cat, User, ApiKey, TradingOffer, CatShow
from .exceptions import PurrrLoveError, AuthenticationError, RateLimitError

__all__ = [
    'PurrrLoveClient',
    'Cat',
    'User', 
    'ApiKey',
    'TradingOffer',
    'CatShow',
    'PurrrLoveError',
    'AuthenticationError',
    'RateLimitError'
]
