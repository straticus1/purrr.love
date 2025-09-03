"""
🐱 Purrr.love Python SDK - Setup
Setup configuration for the Python SDK package
"""

from setuptools import setup, find_packages
import os

# Read the README file
def read_readme():
    readme_path = os.path.join(os.path.dirname(__file__), 'README.md')
    if os.path.exists(readme_path):
        with open(readme_path, 'r', encoding='utf-8') as f:
            return f.read()
    return "🐱 Purrr.love Python SDK - Official Python client library for the Purrr.love cat gaming platform"

# Read requirements
def read_requirements():
    requirements_path = os.path.join(os.path.dirname(__file__), 'requirements.txt')
    if os.path.exists(requirements_path):
        with open(requirements_path, 'r', encoding='utf-8') as f:
            return [line.strip() for line in f if line.strip() and not line.startswith('#')]
    return [
        'requests>=2.25.0',
        'typing-extensions>=3.7.4;python_version<"3.8"'
    ]

setup(
    name="purrr-love-sdk",
    version="1.0.0",
    author="Purrr.love Team",
    author_email="dev@purrr.love",
    description="🐱 Official Python client library for the Purrr.love cat gaming platform",
    long_description=read_readme(),
    long_description_content_type="text/markdown",
    url="https://github.com/purrr-love/python-sdk",
    project_urls={
        "Bug Tracker": "https://github.com/purrr-love/python-sdk/issues",
        "Documentation": "https://docs.purrr.love/python-sdk",
        "Source Code": "https://github.com/purrr-love/python-sdk",
    },
    packages=find_packages(),
    classifiers=[
        "Development Status :: 4 - Beta",
        "Intended Audience :: Developers",
        "License :: OSI Approved :: MIT License",
        "Operating System :: OS Independent",
        "Programming Language :: Python :: 3",
        "Programming Language :: Python :: 3.7",
        "Programming Language :: Python :: 3.8",
        "Programming Language :: Python :: 3.9",
        "Programming Language :: Python :: 3.10",
        "Programming Language :: Python :: 3.11",
        "Programming Language :: Python :: 3.12",
        "Topic :: Software Development :: Libraries :: Python Modules",
        "Topic :: Games/Entertainment",
        "Topic :: Internet :: WWW/HTTP :: Dynamic Content",
        "Topic :: Software Development :: Libraries :: Application Frameworks",
    ],
    python_requires=">=3.7",
    install_requires=read_requirements(),
    extras_require={
        "dev": [
            "pytest>=6.0.0",
            "pytest-cov>=2.10.0",
            "black>=21.0.0",
            "flake8>=3.8.0",
            "mypy>=0.800",
            "sphinx>=3.0.0",
            "sphinx-rtd-theme>=0.5.0",
        ],
        "async": [
            "aiohttp>=3.7.0",
            "asyncio-mqtt>=0.5.0",
        ],
        "websocket": [
            "websockets>=9.0.0",
        ],
    },
    keywords=[
        "cat", "gaming", "api", "client", "sdk", "purrr", "love", "virtual-pets",
        "vr", "ai", "trading", "shows", "multiplayer", "health-monitoring"
    ],
    include_package_data=True,
    zip_safe=False,
    entry_points={
        "console_scripts": [
            "purrr=purrr_love_sdk.cli:main",
        ],
    },
)
