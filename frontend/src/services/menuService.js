import api from "../api/api";

export const getMenus = async () => {
    const response = await api.get("/menus");
    return response.data.data;
};