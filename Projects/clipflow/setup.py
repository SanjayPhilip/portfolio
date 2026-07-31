from setuptools import setup, find_packages

setup(
    name="clipflow",
    version="0.1.0",
    description="A lightweight, smart clipboard manager",
    author="Your Name",
    author_email="your.email@example.com",
    url="https://github.com/YOUR_USERNAME/clipflow",
    packages=find_packages(where="src"),
    package_dir={"": "src"},
    python_requires=">=3.10",
    install_requires=[
        "pyperclip>=1.8.2",
        "pynput>=1.7.6",
        "PyQt6>=6.6.0",
        "SQLAlchemy>=2.0.0",
        "alembic>=1.12.0",
    ],
    extras_require={
        "ai": ["ollama>=0.1.0", "requests>=2.31.0"],
        "dev": [
            "pytest>=7.4.0",
            "pytest-qt>=4.2.0",
            "black>=23.0.0",
            "isort>=5.12.0",
            "flake8>=6.1.0",
            "mypy>=1.7.0",
            "pre-commit>=3.5.0",
        ],
        "build": ["pyinstaller>=6.0.0"],
    },
    entry_points={
        "console_scripts": [
            "clipflow=clipflow.__main__:main",
        ],
    },
    classifiers=[
        "Development Status :: 3 - Alpha",
        "Intended Audience :: End Users/Desktop",
        "License :: OSI Approved :: MIT License",
        "Programming Language :: Python :: 3",
        "Programming Language :: Python :: 3.10",
        "Programming Language :: Python :: 3.11",
        "Programming Language :: Python :: 3.12",
        "Operating System :: OS Independent",
    ],
)
