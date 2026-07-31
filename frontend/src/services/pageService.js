import api from "../api/api";

export const getPages = async () => {
    const response = await api.get("/pages");
    return response.data.data;
};

export const getPage = async (id) => {
    const response = await api.get(`/pages/${id}`);
    return response.data.data;
};