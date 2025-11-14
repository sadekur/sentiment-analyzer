import React from "react";
import ReactDOM from "react-dom/client";

const App = () => {
	return (
		<div>
			Hello World
		</div>
	);
};

export default App;

// ReactDOM.createRoot(document.getElementById("root-menu")).render(
// 	<App />
// );

const rootElement = document.getElementById("root-menu");

if (rootElement) {
	const root = ReactDOM.createRoot(rootElement);
	root.render(<App />);
}
