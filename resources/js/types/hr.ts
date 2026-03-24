/**
 * Shared HR types used across employee and leave-request pages.
 */

export interface Department {
    id: number;
    name: string;
}

/** Minimal employee reference (used in leave-request associations). */
export interface EmployeeSummary {
    id: number;
    first_name: string;
    last_name: string;
}

/** Full employee record for list and detail views. */
export interface Employee extends EmployeeSummary {
    email: string;
    phone: string | null;
    position: string | null;
    status: string;
    department: Department | null;
}

/** Extended employee record for the edit form (includes payroll/contract fields). */
export interface EmployeeEditable extends Employee {
    hire_date: string;
    salary: string | null;
    department_id: number | null;
}

export interface LeaveRequest {
    id: number;
    employee_id: number;
    type: string;
    start_date: string;
    end_date: string;
    reason: string | null;
    status: string;
    employee: EmployeeSummary | null;
}
