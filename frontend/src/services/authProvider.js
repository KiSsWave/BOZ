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


const isTokenExpired = () => {
  const token = getToken();
  if (!token) return true;

  try {
    const decoded = jwtDecode(token);
    return decoded.exp * 1000 < Date.now();
  } catch (error) {
    return true;
  }
};

const getUser = () => {
  const token = getToken();
  if (!token) return null;

  try {
    const decoded = jwtDecode(token);
    if (decoded.exp * 1000 < Date.now()) {
      removeToken();
      return null;
    }
    return { ...decoded, role: localStorage.getItem("role") };
  } catch (error) {
    removeToken();
    return null;
  }
};

const getTokenRemainingTime = () => {
  const token = getToken();
  if (!token) return 0;
  
  try {
    const decoded = jwtDecode(token);
    return Math.max(0, decoded.exp - Math.floor(Date.now() / 1000));
  } catch (error) {
    return 0;
  }
};

const logout = () => {
  removeToken();
}



export { setToken, getToken, removeToken, getUser, logout, isTokenExpired, getTokenRemainingTime };