// src/components/BulkAnalyzer.jsx
import React, { useState } from "react";

const BulkAnalyzer = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [status, setStatus] = useState("");
  const [results, setResults] = useState(null);
  const [error, setError] = useState("");

  const startBulkAnalysis = async () => {
    setIsLoading(true);
    setStatus("Starting bulk analysis on server...");
    setError("");
    setResults(null);

    try {
      const response =await fetch(SENTIMENT_ANALYZER?.apiUrl + "/analyze/bulk",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": SENTIMENT_ANALYZER.nonce, // Critical for auth
          },
          // You can pass filters if you want later
          // body: JSON.stringify({ status: 'publish', limit: 500 }),
        }
      );

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Bulk analysis failed");
      }

      if (data.success === false) {
        throw new Error(data.message || "Analysis completed with errors");
      }

      // Success!
      setResults(data.data); // assuming your backend returns counts
      setStatus("Bulk analysis completed successfully!");
      
    } catch (err) {
      console.error("Bulk analysis error:", err);
      setError(err.message || "Something went wrong.");
      setStatus("");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="mt-8 pt-6 border-t border-gray-200">
      <h3 className="text-lg font-semibold text-gray-900 mb-4">
        Bulk Sentiment Analysis
      </h3>

      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <p className="text-sm text-gray-600 mb-6">
          Run sentiment analysis on <strong>all published posts at once</strong> using your current keyword rules.
        </p>

        {/* Results Summary */}
        {results && (
          <div className="mb-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
            <h4 className="font-semibold text-gray-900 mb-4">Analysis Complete!</h4>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
              <div className="bg-white rounded-lg p-4 shadow-sm">
                <div className="text-3xl font-bold text-green-600">
                  {results.positive || 0}
                </div>
                <div className="text-sm text-gray-600 mt-1">Positive Posts</div>
              </div>
              <div className="bg-white rounded-lg p-4 shadow-sm">
                <div className="text-3xl font-bold text-red-600">
                  {results.negative || 0}
                </div>
                <div className="text-sm text-gray-600 mt-1">Negative Posts</div>
              </div>
              <div className="bg-white rounded-lg p-4 shadow-sm">
                <div className="text-3xl font-bold text-gray-500">
                  {results.neutral || 0}
                </div>
                <div className="text-sm text-gray-600 mt-1">Neutral Posts</div>
              </div>
            </div>
            {results.total && (
              <p className="text-center text-sm text-gray-600 mt-4">
                Total analyzed: <strong>{results.total}</strong> posts
              </p>
            )}
          </div>
        )}

        {/* Error Message */}
        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p className="text-sm text-red-700">{error}</p>
          </div>
        )}

        {/* Status */}
        {status && !results && (
          <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p className="text-sm text-blue-700 flex items-center">
              {/* <span className="inline-block animate-spin w-4 h-4 mr-2 border-2 border-blue-600 border-t-transparent rounded-full" /> */}
              {status}
            </p>
          </div>
        )}

        {/* Button */}
        <button
          onClick={startBulkAnalysis}
          disabled={isLoading}
          className={`
            px-6 py-2 rounded-lg font-semibold text-white text-lg transition-all transform
            ${isLoading
              ? "bg-gray-400 cursor-not-allowed"
              : "bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 shadow-lg hover:shadow-xl"
            }
          `}
        >
          {isLoading ? (
            <>
              <span className="inline-block animate-spin w-5 h-5 mr-3 border-2 border-white border-t-transparent rounded-full" />
              Running Bulk Analysis...
            </>
          ) : (
            "Start Bulk Analysis Now"
          )}
        </button>

        <p className="mt-4 text-xs text-gray-500">
          This runs entirely on the server — fast and safe for thousands of posts.
        </p>
      </div>
    </div>
  );
};

export default BulkAnalyzer;