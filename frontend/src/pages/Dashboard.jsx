import { useEffect, useState, useRef } from "react";
import { useNavigate } from "react-router-dom";

export default function Dashboard({ token, setToken }) {
  const navigate = useNavigate();
  const [data, setData] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [form, setForm] = useState({ id: null, title: "", value: "", category_id: "" });
  const [error, setError] = useState("");
  const [formMode, setFormMode] = useState("add"); // or "edit"
  const [search, setSearch] = useState("");
  const [filterCategory, setFilterCategory] = useState("");
  const [catForm, setCatForm] = useState({ id: null, name: "" });
  const [catMode, setCatMode] = useState("add");
  const [catError, setCatError] = useState("");
  const [user, setUser] = useState(null);
  const [pwForm, setPwForm] = useState({ current_password: "", new_password: "", new_password_confirmation: "" });
  const [pwError, setPwError] = useState("");
  const [pwSuccess, setPwSuccess] = useState("");
  const [pwLoading, setPwLoading] = useState(false);
  const [toast, setToast] = useState({ message: "", type: "", visible: false });
  const toastTimeout = useRef();

  function handleLogout() {
    setToken("");
    navigate("/login");
  }

  // Fetch personal data and categories
  useEffect(() => {
    async function fetchData() {
      setLoading(true);
      try {
        const [dataRes, catRes] = await Promise.all([
          fetch("http://localhost:8000/api/personal-data", {
            headers: { Authorization: `Bearer ${token}` },
          }),
          fetch("http://localhost:8000/api/data-categories", {
            headers: { Authorization: `Bearer ${token}` },
          }),
        ]);
        if (!dataRes.ok || !catRes.ok) throw new Error("Failed to fetch data");
        const dataJson = await dataRes.json();
        const catJson = await catRes.json();
        setData(dataJson.data || []);
        setCategories(catJson.data || []);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    }
    fetchData();
  }, [token]);

  // Fetch user profile
  useEffect(() => {
    async function fetchUser() {
      try {
        const res = await fetch("http://localhost:8000/api/auth/user", {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!res.ok) throw new Error("Failed to fetch user");
        const data = await res.json();
        setUser(data);
      } catch (err) {
        setUser(null);
      }
    }
    fetchUser();
  }, [token]);

  // Handle form input
  function handleChange(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  // Add or update personal data
  async function handleSubmit(e) {
    e.preventDefault();
    setError("");
    try {
      const method = formMode === "add" ? "POST" : "PUT";
      const url =
        formMode === "add"
          ? "http://localhost:8000/api/personal-data"
          : `http://localhost:8000/api/personal-data/${form.id}`;
      const res = await fetch(url, {
        method,
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          title: form.title,
          value: form.value,
          category_id: form.category_id,
        }),
      });
      if (!res.ok) throw new Error("Failed to save data");
      // Refresh data
      setForm({ id: null, title: "", value: "", category_id: "" });
      setFormMode("add");
      setError("");
      // Refetch
      const dataRes = await fetch("http://localhost:8000/api/personal-data", {
        headers: { Authorization: `Bearer ${token}` },
      });
      const dataJson = await dataRes.json();
      setData(dataJson.data || []);
      showToast("Data saved successfully.");
    } catch (err) {
      setError(err.message);
    }
  }

  // Edit
  function handleEdit(entry) {
    setForm({
      id: entry.id,
      title: entry.title,
      value: entry.value,
      category_id: entry.category_id,
    });
    setFormMode("edit");
  }

  // Delete
  async function handleDelete(id) {
    if (!window.confirm("Delete this entry?")) return;
    try {
      const res = await fetch(`http://localhost:8000/api/personal-data/${id}`, {
        method: "DELETE",
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error("Failed to delete");
      setData(data.filter(d => d.id !== id));
      showToast("Data deleted successfully.");
    } catch (err) {
      setError(err.message);
    }
  }

  // Filtered data
  const filteredData = data.filter(entry => {
    const matchesSearch =
      entry.title.toLowerCase().includes(search.toLowerCase()) ||
      entry.value.toLowerCase().includes(search.toLowerCase());
    const matchesCategory =
      !filterCategory || entry.category_id === filterCategory;
    return matchesSearch && matchesCategory;
  });

  // Add or update category
  async function handleCatSubmit(e) {
    e.preventDefault();
    setCatError("");
    try {
      const method = catMode === "add" ? "POST" : "PUT";
      const url =
        catMode === "add"
          ? "http://localhost:8000/api/data-categories"
          : `http://localhost:8000/api/data-categories/${catForm.id}`;
      const res = await fetch(url, {
        method,
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ name: catForm.name }),
      });
      if (!res.ok) throw new Error("Failed to save category");
      setCatForm({ id: null, name: "" });
      setCatMode("add");
      // Refetch categories
      const catRes = await fetch("http://localhost:8000/api/data-categories", {
        headers: { Authorization: `Bearer ${token}` },
      });
      const catJson = await catRes.json();
      setCategories(catJson.data || []);
      showToast("Category saved successfully.");
    } catch (err) {
      setCatError(err.message);
    }
  }

  // Edit category
  function handleCatEdit(cat) {
    setCatForm({ id: cat.id, name: cat.name });
    setCatMode("edit");
  }

  // Delete category
  async function handleCatDelete(id) {
    if (!window.confirm("Delete this category?")) return;
    try {
      const res = await fetch(`http://localhost:8000/api/data-categories/${id}` , {
        method: "DELETE",
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error("Failed to delete category");
      setCategories(categories.filter(c => c.id !== id));
      showToast("Category deleted successfully.");
    } catch (err) {
      setCatError(err.message);
    }
  }

  // Export CSV
  function handleExportCSV() {
    if (!filteredData.length) return;
    const header = ["Title", "Value", "Category"];
    const rows = filteredData.map(entry => [
      `"${entry.title.replace(/"/g, '""')}"`,
      `"${entry.value.replace(/"/g, '""')}"`,
      `"${categories.find(c => c.id === entry.category_id)?.name || "-"}"`,
    ]);
    const csv = [header, ...rows].map(r => r.join(",")).join("\n");
    const blob = new Blob([csv], { type: "text/csv" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "personal-data.csv";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    showToast("CSV exported successfully.");
  }

  // Handle password change
  async function handlePwSubmit(e) {
    e.preventDefault();
    setPwError("");
    setPwSuccess("");
    setPwLoading(true);
    try {
      const res = await fetch("http://localhost:8000/api/auth/change-password", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(pwForm),
      });
      if (!res.ok) {
        const data = await res.json();
        throw new Error(data.message || "Failed to change password");
      }
      setPwSuccess("Password changed successfully.");
      setPwForm({ current_password: "", new_password: "", new_password_confirmation: "" });
      showToast("Password changed successfully.");
    } catch (err) {
      setPwError(err.message);
    } finally {
      setPwLoading(false);
    }
  }

  // Toast notification system
  function showToast(message, type = "success") {
    setToast({ message, type, visible: true });
    clearTimeout(toastTimeout.current);
    toastTimeout.current = setTimeout(() => setToast({ ...toast, visible: false }), 3000);
  }

  // Reset vault
  async function handleResetVault() {
    if (!window.confirm("Delete ALL your personal data? This cannot be undone.")) return;
    try {
      const res = await fetch("http://localhost:8000/api/personal-data", {
        method: "DELETE",
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error("Failed to reset vault");
      setData([]);
      showToast("Vault reset successfully.");
    } catch (err) {
      showToast(err.message, "error");
    }
  }

  // Copy value
  function handleCopyValue(value) {
    navigator.clipboard.writeText(value);
    showToast("Copied to clipboard");
  }

  // Import CSV
  async function handleImportCSV(e) {
    const file = e.target.files[0];
    if (!file) return;
    try {
      const text = await file.text();
      const lines = text.split(/\r?\n/).filter(Boolean);
      if (lines.length < 2) throw new Error("CSV must have at least one data row");
      const [header, ...rows] = lines;
      const headerCols = header.split(",").map(h => h.trim().toLowerCase());
      const titleIdx = headerCols.indexOf("title");
      const valueIdx = headerCols.indexOf("value");
      const catIdx = headerCols.indexOf("category");
      if (titleIdx === -1 || valueIdx === -1 || catIdx === -1) throw new Error("CSV must have Title, Value, Category columns");
      // Build category name to id map
      let catMap = Object.fromEntries(categories.map(c => [c.name.toLowerCase(), c.id]));
      let newCats = {};
      // Create missing categories
      for (const row of rows) {
        const cols = row.split(",");
        const catName = cols[catIdx]?.replace(/"/g, "").trim();
        if (catName && !catMap[catName.toLowerCase()] && !newCats[catName.toLowerCase()]) {
          // Create category
          const res = await fetch("http://localhost:8000/api/data-categories", {
            method: "POST",
            headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
            body: JSON.stringify({ name: catName }),
          });
          if (res.ok) {
            const cat = await res.json();
            catMap[catName.toLowerCase()] = cat.data?.id;
            newCats[catName.toLowerCase()] = true;
          }
        }
      }
      // Import data
      let imported = 0;
      for (const row of rows) {
        const cols = row.split(",");
        const title = cols[titleIdx]?.replace(/"/g, "").trim();
        const value = cols[valueIdx]?.replace(/"/g, "").trim();
        const catName = cols[catIdx]?.replace(/"/g, "").trim();
        const category_id = catMap[catName?.toLowerCase()];
        if (title && value && category_id) {
          const res = await fetch("http://localhost:8000/api/personal-data", {
            method: "POST",
            headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
            body: JSON.stringify({ title, value, category_id }),
          });
          if (res.ok) imported++;
        }
      }
      // Refetch data/categories
      const [dataRes, catRes] = await Promise.all([
        fetch("http://localhost:8000/api/personal-data", { headers: { Authorization: `Bearer ${token}` } }),
        fetch("http://localhost:8000/api/data-categories", { headers: { Authorization: `Bearer ${token}` } }),
      ]);
      setData((await dataRes.json()).data || []);
      setCategories((await catRes.json()).data || []);
      showToast(`Imported ${imported} entries from CSV.`);
    } catch (err) {
      showToast(err.message, "error");
    }
    e.target.value = "";
  }

  return (
    <div className="bg-white p-8 rounded shadow w-full max-w-2xl flex flex-col gap-6">
      <div className="flex justify-between items-center mb-2">
        <h2 className="text-2xl font-bold">Dashboard</h2>
        <button
          onClick={handleLogout}
          className="bg-red-500 text-white px-4 py-2 rounded font-semibold"
        >
          Logout
        </button>
      </div>
      <div className="mb-2 text-gray-700">Welcome to your Personal Data Vault!</div>
      {/* User Profile */}
      <div className="mb-6 border-b pb-4 flex flex-col gap-2 bg-white dark:bg-gray-800 rounded shadow p-4">
        <div className="flex items-center gap-4">
          <div className="bg-blue-100 dark:bg-blue-900 rounded-full w-12 h-12 flex items-center justify-center text-2xl font-bold text-blue-700 dark:text-blue-200">
            {user?.name?.[0]?.toUpperCase() || "U"}
          </div>
          <div>
            <div className="font-semibold text-gray-900 dark:text-gray-100">{user?.name || "User"}</div>
            <div className="text-gray-500 dark:text-gray-400 text-sm">{user?.email || "-"}</div>
          </div>
        </div>
        <form onSubmit={handlePwSubmit} className="flex gap-2 mt-2 flex-wrap items-end">
          <input
            type="password"
            placeholder="Current password"
            className="border p-2 rounded w-48 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
            value={pwForm.current_password}
            onChange={e => setPwForm({ ...pwForm, current_password: e.target.value })}
            required
          />
          <input
            type="password"
            placeholder="New password"
            className="border p-2 rounded w-48 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
            value={pwForm.new_password}
            onChange={e => setPwForm({ ...pwForm, new_password: e.target.value })}
            required
          />
          <input
            type="password"
            placeholder="Confirm new password"
            className="border p-2 rounded w-48 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
            value={pwForm.new_password_confirmation}
            onChange={e => setPwForm({ ...pwForm, new_password_confirmation: e.target.value })}
            required
          />
          <button
            type="submit"
            className="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white rounded p-2 font-semibold w-32 disabled:opacity-50 transition"
            disabled={pwLoading}
          >
            {pwLoading ? "Saving..." : "Change Password"}
          </button>
        </form>
        {pwError && <div className="text-red-600 text-sm">{pwError}</div>}
        {pwSuccess && <div className="text-green-600 text-sm">{pwSuccess}</div>}
      </div>
      {/* Form */}
      <form onSubmit={handleSubmit} className="flex flex-col gap-2 border rounded p-4 bg-white dark:bg-gray-800 shadow mb-4">
        <div className="flex gap-2 flex-wrap">
          <input
            name="title"
            value={form.title}
            onChange={handleChange}
            placeholder="Title"
            className="border p-2 rounded w-1/3 min-w-[120px] bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
            required
          />
          <input
            name="value"
            value={form.value}
            onChange={handleChange}
            placeholder="Value"
            className="border p-2 rounded w-1/3 min-w-[120px] bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
            required
          />
          <select
            name="category_id"
            value={form.category_id}
            onChange={handleChange}
            className="border p-2 rounded w-1/3 min-w-[120px] bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
            required
          >
            <option value="">Select Category</option>
            {categories.map(cat => (
              <option key={cat.id} value={cat.id}>{cat.name}</option>
            ))}
          </select>
        </div>
        <div className="flex gap-2">
          <button
            type="submit"
            className="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white rounded p-2 font-semibold w-32 transition"
          >
            {formMode === "add" ? "Add" : "Update"}
          </button>
          {formMode === "edit" && (
            <button
              type="button"
              className="bg-gray-400 hover:bg-gray-500 dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded p-2 font-semibold w-32 transition"
              onClick={() => {
                setForm({ id: null, title: "", value: "", category_id: "" });
                setFormMode("add");
              }}
            >
              Cancel
            </button>
          )}
        </div>
        {error && <div className="text-red-600 text-sm text-center">{error}</div>}
      </form>
      {/* Search & Filter + Export + Import */}
      <div className="flex gap-2 mb-2 items-center flex-wrap">
        <input
          type="text"
          placeholder="Search..."
          className="border p-2 rounded w-1/2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
          value={search}
          onChange={e => setSearch(e.target.value)}
        />
        <select
          className="border p-2 rounded w-1/2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
          value={filterCategory}
          onChange={e => setFilterCategory(e.target.value)}
        >
          <option value="">All Categories</option>
          {categories.map(cat => (
            <option key={cat.id} value={cat.id}>{cat.name}</option>
          ))}
        </select>
        <button
          className="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800 text-white rounded p-2 font-semibold w-32 transition"
          onClick={handleExportCSV}
          type="button"
          disabled={!filteredData.length}
        >
          Export CSV
        </button>
        <label className="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white rounded p-2 font-semibold w-32 transition cursor-pointer text-center">
          Import CSV
          <input
            type="file"
            accept=".csv"
            onChange={handleImportCSV}
            className="hidden"
          />
        </label>
      </div>
      {/* Data Table */}
      <div className="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow p-4 mb-4">
        {loading ? (
          <div className="text-center text-gray-400 dark:text-gray-500">Loading...</div>
        ) : (
          <table className="min-w-full border text-sm">
            <thead>
              <tr className="bg-gray-100 dark:bg-gray-700">
                <th className="p-2 border">Title</th>
                <th className="p-2 border">Value</th>
                <th className="p-2 border">Category</th>
                <th className="p-2 border">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filteredData.length === 0 ? (
                <tr>
                  <td colSpan={4} className="text-center text-gray-400 dark:text-gray-500 p-4">No data found.</td>
                </tr>
              ) : (
                filteredData.map(entry => (
                  <tr key={entry.id} className="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                    <td className="p-2 border">{entry.title}</td>
                    <td className="p-2 border flex items-center gap-2">
                      <span>{entry.value}</span>
                      <button
                        className="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-2 py-1 rounded text-xs transition"
                        onClick={() => handleCopyValue(entry.value)}
                        type="button"
                        title="Copy value"
                      >
                        Copy
                      </button>
                    </td>
                    <td className="p-2 border">{categories.find(c => c.id === entry.category_id)?.name || "-"}</td>
                    <td className="p-2 border flex gap-2 justify-center">
                      <button
                        className="bg-yellow-400 hover:bg-yellow-500 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white px-2 py-1 rounded text-xs transition"
                        onClick={() => handleEdit(entry)}
                      >
                        Edit
                      </button>
                      <button
                        className="bg-red-500 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-800 text-white px-2 py-1 rounded text-xs transition"
                        onClick={() => handleDelete(entry.id)}
                      >
                        Delete
                      </button>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        )}
      </div>
      {/* Reset Vault */}
      <div className="mb-4 flex justify-end">
        <button
          className="bg-red-500 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-800 text-white rounded p-2 font-semibold transition"
          onClick={handleResetVault}
          type="button"
        >
          Reset Vault
        </button>
      </div>
      {/* Category Management */}
      <div className="mt-6 border-t pt-4 bg-white dark:bg-gray-800 rounded shadow p-4">
        <h3 className="font-semibold mb-2 text-gray-900 dark:text-gray-100">Manage Categories</h3>
        <form onSubmit={handleCatSubmit} className="flex gap-2 mb-2 flex-wrap">
          <input
            type="text"
            placeholder="Category name"
            className="border p-2 rounded w-1/2 min-w-[120px] bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-400"
            value={catForm.name}
            onChange={e => setCatForm({ ...catForm, name: e.target.value })}
            required
          />
          <button
            type="submit"
            className="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white rounded p-2 font-semibold w-32 transition"
          >
            {catMode === "add" ? "Add" : "Update"}
          </button>
          {catMode === "edit" && (
            <button
              type="button"
              className="bg-gray-400 hover:bg-gray-500 dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded p-2 font-semibold w-32 transition"
              onClick={() => {
                setCatForm({ id: null, name: "" });
                setCatMode("add");
              }}
            >
              Cancel
            </button>
          )}
        </form>
        {catError && <div className="text-red-600 text-sm mb-2">{catError}</div>}
        <ul className="divide-y divide-gray-200 dark:divide-gray-700">
          {categories.map(cat => (
            <li key={cat.id} className="flex justify-between items-center py-1">
              <span className="text-gray-900 dark:text-gray-100">{cat.name}</span>
              <span className="flex gap-2">
                <button
                  className="bg-yellow-400 hover:bg-yellow-500 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white px-2 py-1 rounded text-xs transition"
                  onClick={() => handleCatEdit(cat)}
                >
                  Edit
                </button>
                <button
                  className="bg-red-500 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-800 text-white px-2 py-1 rounded text-xs transition"
                  onClick={() => handleCatDelete(cat.id)}
                >
                  Delete
                </button>
              </span>
            </li>
          ))}
        </ul>
      </div>
      {/* Toast Notification */}
      {toast.visible && (
        <div className={`fixed top-4 right-4 z-50 px-4 py-2 rounded shadow text-white font-semibold transition-all ${toast.type === "success" ? "bg-green-600" : "bg-red-600"}`}>
          {toast.message}
        </div>
      )}
    </div>
  );
} 