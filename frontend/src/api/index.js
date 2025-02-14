import axios from "axios";


const apiClient = axios.create({
  baseURL: "http://localhost:44050",
  headers: {
    "Content-Type": "application/json",

  },
});

apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default apiClient;
