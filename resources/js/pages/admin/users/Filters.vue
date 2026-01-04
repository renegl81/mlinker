<template>
    <Collapsible v-model:open="isOpen">
        <div class="flex items-center justify-between">
            <CollapsibleTrigger as-child>
                <Button variant="ghost" size="sm">
                    <Filter class="mr-2 h-4 w-4" />
                    {{ isOpen ? 'Ocultar filtros' : 'Mostrar filtros' }}
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
                Limpiar filtros
            </Button>
        </div>
        <CollapsibleContent class="mt-4">
            <Card>
                <CardContent class="pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="filter-name">Nombre</Label>
                            <Input
                                id="filter-name"
                                v-model="localFilters.name"
                                placeholder="Buscar por nombre"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-lastname">Apellido</Label>
                            <Input
                                id="filter-lastname"
                                v-model="localFilters.last_name"
                                placeholder="Buscar por apellido"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-email">Email</Label>
                            <Input
                                id="filter-email"
                                type="email"
                                v-model="localFilters.email"
                                placeholder="Buscar por email"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-phone">Teléfono</Label>
                            <Input
                                id="filter-phone"
                                v-model="localFilters.phone"
                                placeholder="Buscar por teléfono"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-country">País</Label>
                            <Input
                                id="filter-country"
                                v-model="localFilters.country"
                                placeholder="Buscar por país"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-province">Provincia</Label>
                            <Input
                                id="filter-province"
                                v-model="localFilters.province"
                                placeholder="Buscar por provincia"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-city">Ciudad</Label>
                            <Input
                                id="filter-city"
                                v-model="localFilters.city"
                                placeholder="Buscar por ciudad"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-postal">Código Postal</Label>
                            <Input
                                id="filter-postal"
                                v-model="localFilters.postal_code"
                                placeholder="Buscar por código postal"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="filter-address">Dirección</Label>
                            <Input
                                id="filter-address"
                                v-model="localFilters.address"
                                placeholder="Buscar por dirección"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <Button variant="outline" @click="clearFilters">
                            <X class="mr-2 h-4 w-4" />
                            Limpiar
                        </Button>
                        <Button @click="applyFilters">
                            <Search class="mr-2 h-4 w-4" />
                            Buscar
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
