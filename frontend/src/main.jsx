import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.jsx'

// Mount the React app to the div created by view.php
const container = document.getElementById('vibeyourcourse-react-app') || document.getElementById('root');

if (container) {
  createRoot(container).render(
    <StrictMode>
      <App />
    </StrictMode>,
  );
} else {
  console.error('Could not find container element for VibeyourCourse React app');
}
