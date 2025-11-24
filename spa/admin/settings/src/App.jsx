import React, { useState, useEffect } from "react";
import { createRoot } from "react-dom/client";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

// Import your pages
import Dashboard from "./pages/Dashboard";
import Help from "./pages/Help";
import Sentiments from "./pages/Sentiments";

// Optional: fallback component
const NotFound = () => (
    <div className="p-8 text-center">
        <h2>Page Not Found</h2>
        <p>The requested tab does not exist.</p>
    </div>
);

const App = () => {
    const validTabs = ["/sentiments", "/settings", "/help"];
    const defaultTab = "/sentiments"; // change if you want Dashboard as default

    const getInitialTab = () => {
        const hash = window.location.hash.replace("#", "");
        return validTabs.includes(hash) ? hash : defaultTab;
    };

    const [activeTab, setActiveTab] = useState(getInitialTab);

    useEffect(() => {
        const handleHashChange = () => {
            const hash = window.location.hash.replace("#", "");
            if (validTabs.includes(hash)) {
                setActiveTab(hash);
            } else if (hash === "") {
                setActiveTab(defaultTab);
                window.location.hash = defaultTab;
            } else {
                // Invalid route → redirect to default
                setActiveTab(defaultTab);
                window.location.hash = defaultTab;
            }
        };

        window.addEventListener("hashchange", handleHashChange);
        handleHashChange(); // run on mount

        return () => window.removeEventListener("hashchange", handleHashChange);
    }, []);

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Optional: Top Tab Navigation (you can style it like EasyCommerce) */}
            <div className="border-b bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <nav className="flex space-x-8" aria-label="Tabs">
                        <a
                            href="#/sentiments"
                            className={`py-4 px-1 border-b-2 font-medium text-sm ${
                                activeTab === "/sentiments"
                                    ? "border-indigo-500 text-indigo-600"
                                    : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            }`}
                        >
                            Sentiments
                        </a>
                        <a
                            href="#/settings"
                            className={`py-4 px-1 border-b-2 font-medium text-sm ${
                                activeTab === "/settings"
                                    ? "border-indigo-500 text-indigo-600"
                                    : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            }`}
                        >
                            Settings
                        </a>
                        {/* <a
                            href="#/help"
                            className={`py-4 px-1 border-b-2 font-medium text-sm ${
                                activeTab === "/help"
                                    ? "border-indigo-500 text-indigo-600"
                                    : "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            }`}
                        >
                            Help
                        </a> */}
                    </nav>
                </div>
            </div>

            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {activeTab === "/sentiments" && <Sentiments />}
                    {activeTab === "/settings" && <Settings />}
                    {/* {activeTab === "/help" && <Help />} */}
                    {!validTabs.includes(activeTab) && <NotFound />}
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