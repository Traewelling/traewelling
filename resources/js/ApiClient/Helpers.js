export function catchError(error) {
    let errors   = [];
    let response = {};
    if (error.response.data.errors) {
        Object.entries(error.response.data.errors).forEach((err) => {
            errors.push(err[1][0]);
        });
    } else {
        errors.push(error.response.data.message);
    }

    response['status'] = error.response.status;
    response['errors'] = errors;

    return response;
}
