import {UserAuthResource} from "../../types/Api.gen";

export class RoleHelper {
    private readonly user: UserAuthResource | null;

    constructor(user: UserAuthResource | null) {
        this.user = user;
    }

    hasRole(role: string): boolean {
        if (!this.user?.roles) {
            console.warn('RoleHelper: User roles are not defined.');
            return false;
        }
        return this.user.roles.includes(role);
    }

    admin(): boolean {
        return this.hasRole('admin');
    }

    beta(): boolean {
        return this.hasRole('open-beta');
    }

    privateBeta(): boolean {
        return this.hasRole('private-beta');
    }
}
