import React, { useState, useEffect } from "react";
import ReactDOM from "react-dom/client";
import Analyzer from "./pages/Analyzer";

const App = () => {


	return (
		<Analyzer />
	);
};

export default App;

const rootElement = document.getElementById("root-menu");

if (rootElement) {
	const root = ReactDOM.createRoot(rootElement);
	root.render(<App />);
}
