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
                            <Label for="filter-status">{{ t('common.status') }}</Label>
                            <SelectRoot
                                v-model="localFilters.is_active"
                            >
                                <SelectTrigger id="filter-status">
                                    <SelectValue :placeholder="t('panel.filters.select_status')" />
                                </SelectTrigger>
                                <SelectPortal>
                                    <SelectContent>
                                        <SelectViewport class="p-1">
                                            <SelectItem value="">
                                                <SelectItemText>{{ t('panel.filters.all_statuses') }}</SelectItemText>
                                            </SelectItem>
                                            <SelectItem value="1">
                                                <SelectItemText>{{ t('common.active') }}</SelectItemText>
                                            </SelectItem>
                                            <SelectItem value="0">
                                                <SelectItemText>{{ t('common.inactive') }}</SelectItemText>
                                            </SelectItem>
                                        </SelectViewport>
                                    </SelectContent>
                                </SelectPortal>
                            </SelectRoot>
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
import {
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectPortal,
    SelectContent,
    SelectViewport,
    SelectItem,
    SelectItemText
} from 'reka-ui'
import { Filter, ChevronDown, X, Search } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

export interface MenuFilters {
    name?: string
    is_active?: string
}

interface Props {
    modelValue: MenuFilters
}

const props = defineProps<Props>()

const emit = defineEmits<{
    'update:modelValue': [value: MenuFilters]
    'apply': [value: MenuFilters]
    'clear': []
}>()

const isOpen = ref(false)
const localFilters = ref<MenuFilters>({ ...props.modelValue })

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
