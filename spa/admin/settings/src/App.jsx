import React, { useState, useEffect } from "react";
import { createRoot } from "react-dom/client";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

// Import your pages
import Overview from "./pages/Overview"; // New Overview/Landing page
import Dashboard from "./pages/Dashboard";
import Sentiments from "./pages/Sentiments";
import Settings from "./pages/Settings";

const App = () => {
    const [activeTab, setActiveTab] = useState("");

    useEffect(() => {
        const handleHashChange = () => {
            const hash = window.location.hash.replace("#", "") || "";
            setActiveTab(hash);
        };

        window.addEventListener("hashchange", handleHashChange);
        handleHashChange();

        return () => window.removeEventListener("hashchange", handleHashChange);
    }, []);

    const renderContent = () => {
        switch (activeTab) {
            case "":
            case "/":
                return <Overview />; // Main menu page (no hash)
            case "/dashboard":
                return <Dashboard />; // Dashboard with hash
            case "/sentiments":
                return <Sentiments />;
            case "/settings":
                return <Settings />;
            default:
                return <Overview />;
        }
    };

    return (
        <div className="min-h-screen bg-gray-50">
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {renderContent()}
                </div>
            </div>

            <ToastContainer position="bottom-right" />
        </div>
    );
};

// Mount React app
const container = document.getElementById("sentiment-root");
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}