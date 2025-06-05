# Digital Wallet Frontend

This is the frontend for the Digital Wallet application, built using Vue 3 and configured to run with Docker.

## 🧰 Requirements

- Docker
- Docker Compose

## 🚀 Running the Project

1. Clone the repository:

```bash
git clone https://your-repo-url.git
cd your-repo-directory
```

2. Build and run the container:

```bash
docker-compose up --build
```

3. Access the application in your browser:

```
http://localhost:5173
```

Make sure the backend is running and accessible, typically at `http://localhost:8000`.

## 📁 Project Structure

- `src/views`: Contains main views like Login, Dashboard, Transactions, Revert, and Register.
- `src/components`: Reusable components like Alert.
- `src/api.js`: Axios configuration with bearer token handling.
- `router/index.js`: Vue Router configuration with protected routes.

## 📝 Notes

- Login is required to access protected routes.
- You can register a new user via the Register page.
- Error handling is implemented and displayed at the top of each page.