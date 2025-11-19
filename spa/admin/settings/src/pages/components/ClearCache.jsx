import React from "react";

const ClearCache = () => {
  return (
    <div className="mt-8 pt-6 border-t border-gray-200">
      <h2 className="text-lg font-semibold text-gray-700 mb-3">Clear Cache</h2>
      <p className="text-gray-600">
        This action will clear the sentiment cache for all posts.
      </p>
      <button
        // onClick={handleClearCache}
        className="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
      >
        Clear Cache
      </button>
    </div>
  );
};

export default ClearCache;
