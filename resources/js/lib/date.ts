import { format, isValid, parseISO } from 'date-fns';

const parseDate = (value: string) => {
  if (!value) return null;
  const iso = parseISO(value);
  if (isValid(iso)) return iso;
  const native = new Date(value);
  return isValid(native) ? native : null;
};

export const formatDate = (value?: string) => {
  if (!value) return '-';
  const date = parseDate(value);
  return date ? format(date, 'dd-MM-yyyy') : value;
};

export const formatDateTime = (value?: string) => {
  if (!value) return '-';
  const date = parseDate(value);
  return date ? format(date, 'dd-MM-yyyy HH:mm') : value;
};
