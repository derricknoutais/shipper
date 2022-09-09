@extends('layouts.welcome')


@section('content')
    <handle-index :handles_prop="{{ $handles }}" inline-template>
        <div class="tw-container tw-mx-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Traduction</th>
                        <th>Affichage (Version Anglaise)</th>
                        <th>Exemple</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="handle in handles">
                        <td scope="row">
                            @{{ handle.name }}
                        </td>
                        <td>
                            <input type="text" v-model="handle.translation" class="form-control tw-ml-2" @change="updateDisplay(handle, 'translation')">
                        </td>
                        <td class="tw-flex">
                            <select v-model="handle.display1" class="form-control tw-ml-2" @change="updateDisplay(handle, 'display1')">
                                <option value="">Vide</option>
                                <option value="variant_option_one_value">Option 1</option>
                                <option value="variant_option_two_value">Option 2</option>
                                <option value="variant_option_three_value">Option 3</option>
                            </select>
                            <select v-model="handle.display2" class="form-control tw-ml-2" @change="updateDisplay(handle, 'display2')">
                                <option value="">Vide</option>
                                <option value="variant_option_one_value">Option 1</option>
                                <option value="variant_option_two_value">Option 2</option>
                                <option value="variant_option_three_value">Option 3</option>
                            </select>
                            <select v-model="handle.display3" class="form-control tw-ml-2" @change="updateDisplay(handle, 'display3')">
                                <option value="">Vide</option>
                                <option value="variant_option_one_value">Option 1</option>
                                <option value="variant_option_two_value">Option 2</option>
                                <option value="variant_option_three_value">Option 3</option>
                            </select>
                        </td>
                        <td v-if="handle.product_example">
                            @{{ handle.translation }}
                            @{{ handle.product_example[handle.display1] }}
                            @{{ handle.product_example[handle.display2] }}
                            @{{ handle.product_example[handle.display3] }}
                        </td>
                    </tr>
                    <tr>
                        <td scope="row"></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </handle-index>
@endsection
