import {
  BrowserRouter as Router,
  Routes,
  Route,
  Navigate,
} from "react-router-dom";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Dashboard from "./pages/Dashboard";
import { useEffect, useState } from "react";

function App() {
  const [token, setToken] = useState(() => localStorage.getItem("token"));
  const [dark, setDark] = useState(() => localStorage.getItem("dark") === "true");

  // Simple auth guard
  function PrivateRoute({ children }) {
    return token ? children : <Navigate to="/login" />;
  }

  useEffect(() => {
    if (token) localStorage.setItem("token", token);
    else localStorage.removeItem("token");
  }, [token]);

  useEffect(() => {
    localStorage.setItem("dark", dark);
    if (dark) document.documentElement.classList.add("dark");
    else document.documentElement.classList.remove("dark");
  }, [dark]);

  return (
    <Router>
      <div className={`min-h-screen flex flex-col items-center justify-center transition-colors duration-300 ${dark ? "bg-gray-900" : "bg-gray-50"}`}>
        <button
          className="fixed top-4 left-4 z-50 bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 px-3 py-1 rounded shadow hover:bg-gray-300 dark:hover:bg-gray-700 transition"
          onClick={() => setDark(d => !d)}
        >
          {dark ? "☀️ Light" : "🌙 Dark"}
        </button>
        <Routes>
          <Route path="/login" element={<Login setToken={setToken} />} />
          <Route path="/register" element={<Register setToken={setToken} />} />
          <Route
            path="/"
            element={
              <PrivateRoute>
                <Dashboard token={token} setToken={setToken} />
              </PrivateRoute>
            }
          />
          <Route path="*" element={<Navigate to={token ? "/" : "/login"} />} />
        </Routes>
      </div>
    </Router>
  );
}

export default App;
