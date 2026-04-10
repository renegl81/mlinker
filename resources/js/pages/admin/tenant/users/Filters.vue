<template>
    <Collapsible v-model:open="isOpen">
        <div class="flex items-center justify-between">
            <CollapsibleTrigger as-child>
                <Button variant="ghost" size="sm">
                    <Filter class="mr-2 h-4 w-4" />
                    {{ isOpen ? t('panel.common.hide_filters') : t('panel.common.show_filters') }}
                    <ChevronDown
                        class="ml-2 h-4 w-4 transition-transform"
                        :class="{ 'rotate-180': isOpen }"
                    />
                </Button>
            </CollapsibleTrigger>
            <Button
                v-if="hasActiveFilters"
                variant="ghost"
                size="sm"
                @click="clearFilters"
            >
                <X class="mr-2 h-4 w-4" />
                {{ t('panel.common.clear_filters') }}
            </Button>
        </div>
        <CollapsibleContent class="mt-4">
            <Card>
                <CardContent class="pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="filter-name">{{ t('common.name') }}</Label>
                            <Input
                                id="filter-name"
                                v-model="localFilters.name"
                                :placeholder="t('panel.filters.search_name')"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-lastname">{{ t('panel.common.last_name') }}</Label>
                            <Input
                                id="filter-lastname"
                                v-model="localFilters.last_name"
                                :placeholder="t('panel.filters.search_surname')"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-email">Email</Label>
                            <Input
                                id="filter-email"
                                type="email"
                                v-model="localFilters.email"
                                :placeholder="t('panel.filters.search_email')"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-phone">{{ t('panel.common.phone') }}</Label>
                            <Input
                                id="filter-phone"
                                v-model="localFilters.phone"
                                :placeholder="t('panel.filters.search_phone')"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-country">{{ t('panel.common.country') }}</Label>
                            <Input
                                id="filter-country"
                                v-model="localFilters.country"
                                :placeholder="t('panel.filters.search_country')"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-province">{{ t('panel.common.province') }}</Label>
                            <Input
                                id="filter-province"
                                v-model="localFilters.province"
                                :placeholder="t('panel.filters.search_province')"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-city">{{ t('panel.common.city') }}</Label>
                            <Input
                                id="filter-city"
                                v-model="localFilters.city"
                                :placeholder="t('panel.filters.search_city')"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-postal">{{ t('panel.common.postal_code') }}</Label>
                            <Input
                                id="filter-postal"
                                v-model="localFilters.postal_code"
                                :placeholder="t('panel.filters.search_postal')"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-address">{{ t('panel.common.address') }}</Label>
                            <Input
                                id="filter-address"
                                v-model="localFilters.address"
                                :placeholder="t('panel.filters.search_address')"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <Button variant="outline" @click="clearFilters">
                            <X class="mr-2 h-4 w-4" />
                            {{ t('panel.common.clear') }}
                        </Button>
                        <Button @click="applyFilters">
                            <Search class="mr-2 h-4 w-4" />
                            {{ t('panel.filters.search_button') }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </CollapsibleContent>
    </Collapsible>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent } from '@/components/ui/card'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Filter, ChevronDown, X, Search } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

export interface UserFilters {
    name?: string
    last_name?: string
    email?: string
    phone?: string
    country?: string
    province?: string
    city?: string
    postal_code?: string
    address?: string
}

interface Props {
    modelValue: UserFilters
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'update:modelValue': [value: UserFilters]
    'apply': [value: UserFilters]
    'clear': []
}>()

const isOpen = ref(false)
const localFilters = ref<UserFilters>({ ...props.modelValue })

watch(() => props.modelValue, (newVal) => {
    localFilters.value = { ...newVal }
}, { deep: true })

const hasActiveFilters = computed(() => {
    return Object.values(props.modelValue).some(value => value && value.length > 0)
})

function applyFilters() {
    emit('update:modelValue', { ...localFilters.value })
    emit('apply', { ...localFilters.value })
}

function clearFilters() {
    localFilters.value = {}
    emit('clear')
    emit('update:modelValue', {})
    emit('apply', {})
}
</script>
