import {jwtDecode} from 'jwt-decode';

const setToken = (token) => {
  localStorage.setItem("token", token);
  const decoded = jwtDecode(token);
  localStorage.setItem("role", decoded.role);
};

const getToken = () => {
  return localStorage.getItem("token");
};

const removeToken = () => {
  localStorage.removeItem("token");
  localStorage.removeItem("role");
};

const getUser = () => {
  const token = getToken();
  if (!token) return null;

  try {
    const decoded = jwtDecode(token);
    return { ...decoded, role: localStorage.getItem("role") };
  } catch (error) {
    removeToken();
    return null;
  }
};

const logout = () => {
  removeToken();
}

export { setToken, getToken, removeToken, getUser, logout };
