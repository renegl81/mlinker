<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    PinInput,
    PinInputGroup,
    PinInputSlot,
} from '@/components/ui/pin-input';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/two-factor/login';
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
}

const showRecoveryInput = ref<boolean>(false);

const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: t('auth.two_factor.recovery_title'),
            description: t('auth.two_factor.recovery_description'),
            toggleText: t('auth.two_factor.use_code'),
        };
    }

    return {
        title: t('auth.two_factor.code_title'),
        description: t('auth.two_factor.code_description'),
        toggleText: t('auth.two_factor.use_recovery'),
    };
});

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = [];
};

const code = ref<number[]>([]);
const codeValue = computed<string>(() => code.value.join(''));
</script>

<template>
    <AuthLayout
        :title="authConfigContent.title"
        :description="authConfigContent.description"
    >
        <Head :title="t('auth.two_factor.head')" />

        <div class="space-y-6">
            <template v-if="!showRecoveryInput">
                <Form
                    v-bind="store.form()"
                    class="space-y-5"
                    reset-on-error
                    @error="code = []"
                    #default="{ errors, processing, clearErrors }"
                >
                    <input type="hidden" name="code" :value="codeValue" />
                    <div
                        class="flex flex-col items-center justify-center space-y-3 text-center"
                    >
                        <div class="flex w-full items-center justify-center">
                            <PinInput
                                id="otp"
                                placeholder="○"
                                v-model="code"
                                type="number"
                                otp
                            >
                                <PinInputGroup>
                                    <PinInputSlot
                                        v-for="(id, index) in 6"
                                        :key="id"
                                        :index="index"
                                        :disabled="processing"
                                        autofocus
                                    />
                                </PinInputGroup>
                            </PinInput>
                        </div>
                        <InputError :message="errors.code" />
                    </div>
                    <Button
                        type="submit"
                        class="h-11 w-full rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/20 hover:bg-teal-600"
                        :disabled="processing"
                    >
                        {{ t('auth.two_factor.submit') }}
                    </Button>
                    <div class="text-center text-sm text-slate-500">
                        <span>{{ t('auth.two_factor.or_you_can') }} </span>
                        <button
                            type="button"
                            class="font-medium text-teal-600 underline-offset-4 hover:text-teal-700 hover:underline"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfigContent.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>

            <template v-else>
                <Form
                    v-bind="store.form()"
                    class="space-y-5"
                    reset-on-error
                    #default="{ errors, processing, clearErrors }"
                >
                    <Input
                        name="recovery_code"
                        type="text"
                        :placeholder="t('auth.two_factor.recovery_placeholder')"
                        :autofocus="showRecoveryInput"
                        required
                    />
                    <InputError :message="errors.recovery_code" />
                    <Button
                        type="submit"
                        class="h-11 w-full rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/20 hover:bg-teal-600"
                        :disabled="processing"
                    >
                        {{ t('auth.two_factor.submit') }}
                    </Button>

                    <div class="text-center text-sm text-slate-500">
                        <span>{{ t('auth.two_factor.or_you_can') }} </span>
                        <button
                            type="button"
                            class="font-medium text-teal-600 underline-offset-4 hover:text-teal-700 hover:underline"
                            @click="() => toggleRecoveryMode(clearErrors)"
                        >
                            {{ authConfigContent.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>
