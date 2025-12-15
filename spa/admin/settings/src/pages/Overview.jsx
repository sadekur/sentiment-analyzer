import React from "react";

const Overview = () => {
    const navigateToDashboard = () => {
        window.location.hash = "#/dashboard";
    };

    const navigateToSentiments = () => {
        window.location.hash = "#/sentiments";
    };

    return (
        <div className="bg-white rounded-lg shadow">
            <div className="p-6">
                <h1 className="text-3xl font-bold mb-4 text-gray-900">
                    Welcome to Sentiment Analyzer
                </h1>
                <p className="text-gray-600 mb-8">
                    Your all-in-one solution for analyzing customer sentiments and feedback.
                </p>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {/* Dashboard Card */}
                    <div 
                        onClick={navigateToDashboard}
                        className="border rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-4xl">📊</span>
                        </div>
                        <h3 className="text-xl font-semibold mb-2">Dashboard</h3>
                        <p className="text-gray-600 text-sm">
                            View analytics, charts, and insights about your sentiment data.
                        </p>
                    </div>

                    {/* Sentiments Card */}
                    <div 
                        onClick={navigateToSentiments}
                        className="border rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-4xl">💭</span>
                        </div>
                        <h3 className="text-xl font-semibold mb-2">All Sentiments</h3>
                        <p className="text-gray-600 text-sm">
                            Browse and manage all sentiment analysis results.
                        </p>
                    </div>

                    {/* Settings Card */}
                    <div 
                        onClick={() => window.location.hash = "#/settings"}
                        className="border rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-4xl">⚙️</span>
                        </div>
                        <h3 className="text-xl font-semibold mb-2">Settings</h3>
                        <p className="text-gray-600 text-sm">
                            Configure your sentiment analyzer preferences.
                        </p>
                    </div>
                </div>

                {/* Quick Stats Section */}
                <div className="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="bg-blue-50 rounded-lg p-4">
                        <div className="text-2xl font-bold text-blue-600">1,234</div>
                        <div className="text-sm text-gray-600">Total Analyses</div>
                    </div>
                    <div className="bg-green-50 rounded-lg p-4">
                        <div className="text-2xl font-bold text-green-600">856</div>
                        <div className="text-sm text-gray-600">Positive</div>
                    </div>
                    <div className="bg-yellow-50 rounded-lg p-4">
                        <div className="text-2xl font-bold text-yellow-600">234</div>
                        <div className="text-sm text-gray-600">Neutral</div>
                    </div>
                    <div className="bg-red-50 rounded-lg p-4">
                        <div className="text-2xl font-bold text-red-600">144</div>
                        <div className="text-sm text-gray-600">Negative</div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Overview;