declare module '../../js/api/api' {
    export default class API {
        static request(path: string, method?: string, data?: object, customErrorHandling?: boolean): Promise<Response>;
    }
}
