@php use App\Models\EyepieceMake; @endphp
<x-app-layout>
    <div>
        <div>
            <div
                class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
            >
                <h2 class="text-xl font-semibold leading-tight">
                    {{ __("Update ") . $type->name }}
                </h2>
                <div class="mt-2">
                    <x-card>
                        <form
                            role="form"
                            action="{{ route("eyepiecetype.store") }}"
                            method="POST"
                        >
                            @csrf
                            <div class="col-span-6 sm:col-span-5">
                                <input name="id"
                                       type="hidden"
                                       value="{{ $type->id }}"
                                />
                                <x-input
                                    name="eyepiece_type"
                                    label="{!! __('Type') !!}"
                                    type="text"
                                    class="mt-1 block w-full"
                                    value="{{ $type->name }}"
                                    id="eyepiece_type"
                                />
                                <x-input
                                    name="eyepiece_make"
                                    label="{!! __('Make') !!}"
                                    type="text"
                                    class="mt-1 block w-full"
                                    value="{{ EyepieceMake::where('id', $type->eyepiece_makes_id)->first()->name }}"
                                    disabled
                                    id="eyepiece_make"
                                />
                                <x-button class="mt-5"
                                          type="submit"
                                          secondary
                                          name="update"
                                          label="{{ __('Update type') }}"
                                />
                            </div>
                        </form>
                        <br/>
                        <hr/>
                        <br/>
                        <form
                            role="form"
                            action="{{ route("eyepiecetype.destroy") }}"
                            method="POST"
                        >
                            @csrf
                            <div class="col-span-6 sm:col-span-5">
                                <input name="id"
                                       type="hidden"
                                       value="{{ $type->id }}"
                                />

                                <x-button class="mt-5"
                                          type="submit"
                                          secondary
                                          name="delete"
                                          label="{{ __('Delete type and remove selected type from all eyepieces') }}"
                                />

                            </div>

                        </form>
                    </x-card>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
