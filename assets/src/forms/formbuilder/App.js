import Alpine from 'alpinejs';

import {FormBuilder} from './FormBuilder';
import {FormField} from './FormField';

window.Alpine = Alpine;

Alpine.data('FormBuilder', FormBuilder);
Alpine.data('FormField', FormField);

Alpine.start();
