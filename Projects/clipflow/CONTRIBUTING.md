# 🤝 Contributing to ClipFlow

Thank you for your interest in contributing to ClipFlow! This document will help you get started.

## 🚀 Quick Start

1. **Fork** the repository on GitHub
2. **Clone** your fork locally
3. **Install** development dependencies:
   ```bash
   pip install -e ".[dev]"
   pre-commit install
   ```
4. **Create** a new branch for your feature
5. **Make** your changes
6. **Run** tests: `pytest`
7. **Commit** using conventional commits
8. **Push** and open a Pull Request

## 📋 Commit Message Convention

We use [Conventional Commits](https://www.conventionalcommits.org/):

| Type | Description |
|------|-------------|
| `feat:` | New feature |
| `fix:` | Bug fix |
| `docs:` | Documentation only |
| `style:` | Code style (formatting) |
| `refactor:` | Code refactoring |
| `test:` | Adding tests |
| `chore:` | Maintenance tasks |

**Examples:**
- `feat: add system tray integration`
- `fix: handle empty clipboard content`
- `docs: update installation instructions`

## 🧪 Testing

```bash
# Run all tests
pytest

# Run with coverage
pytest --cov=clipflow

# Run specific test file
pytest tests/test_models.py
```

## 🎨 Code Style

We use `black` and `isort` for formatting:

```bash
# Format all code
black src/ tests/
isort src/ tests/

# Check formatting (CI does this)
black --check src/ tests/
```

## 🐛 Reporting Issues

When reporting bugs, please include:
- Operating system and version
- Python version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable

## 📜 License

By contributing, you agree that your contributions will be licensed under the MIT License.
