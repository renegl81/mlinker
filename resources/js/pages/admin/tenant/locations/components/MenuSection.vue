<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create } from '@/routes/tenant/locations/menus';
import { show as menuShow } from '@/routes/tenant/menus';
import type { Menu } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Edit,
    Image as ImageIcon,
    Plus,
    Utensils,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Props {
    locationId: number;
    menus?: Menu[];
}

const props = defineProps<Props>();
const page = usePage();
const messages = computed(() => page.props.messages as any);

</script>

<template>
    <Card>
        <CardHeader
            class="flex flex-row items-center justify-between space-y-0"
        >
            <CardTitle class="flex items-center gap-2 text-lg">
                <Utensils class="h-5 w-5 text-primary" />
                {{ messages.menus?.plural || 'Menús' }}
            </CardTitle>
            <Button size="sm" as-child>
                <Link :href="create(props.locationId)">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ messages.menus?.actions?.create || 'Nuevo Menú' }}
                </Link>
            </Button>
        </CardHeader>
        <CardContent>
            <div v-if="menus && menus.length > 0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-16"></TableHead>
                            <TableHead>{{
                                messages.menus?.fields?.name
                            }}</TableHead>
                            <TableHead>{{
                                messages.menus?.fields?.is_active
                            }}</TableHead>
                            <TableHead class="text-right">{{
                                messages.actions?.label
                            }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="menu in menus" :key="menu.id">
                            <TableCell>
                                <div
                                    class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-md border bg-muted"
                                >
                                    <img
                                        v-if="menu.image_path"
                                        :src="menu.image_path"
                                        :alt="menu.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <ImageIcon
                                        v-else
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                </div>
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ menu.name }}
                                <p
                                    v-if="menu.description"
                                    class="line-clamp-1 text-xs font-normal text-muted-foreground"
                                >
                                    {{ menu.description }}
                                </p>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        menu.is_active ? 'default' : 'secondary'
                                    "
                                >
                                    {{ menu.is_active ? t('common.active') : t('common.inactive') }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    as-child
                                >
                                    <Link :href="menuShow(menu.id).url">
                                        <Edit class="h-4 w-4" />
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            <div
                v-else
                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed py-10 text-center"
            >
                <Utensils class="mb-3 h-10 w-10 text-muted-foreground/30" />
                <p class="text-sm font-medium text-muted-foreground">
                    {{
                        messages.menus?.empty ||
                        'No hay menús para esta ubicación.'
                    }}
                </p>
            </div>
        </CardContent>

    </Card>
</template>
