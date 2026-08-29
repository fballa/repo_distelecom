class ApiService {
  constructor() {
    this.baseURL = '';
    this.client = axios.create({ timeout: 10000 });
  }

  setBaseURL(url) {
    this.baseURL = url;
    this.client = axios.create({ baseURL: url, timeout: 10000 });
  }

  async get(endpoint) {
    return this.client.get(endpoint);
  }

  async post(endpoint, data) {
    return this.client.post(endpoint, data);
  }

  async put(endpoint, data) {
    return this.client.put(endpoint, data);
  }

  async delete(endpoint) {
    return this.client.delete(endpoint);
  }
}

export const api = new ApiService();
