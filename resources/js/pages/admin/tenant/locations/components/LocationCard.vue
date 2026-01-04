<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { edit, show } from '@/routes/tenant/locations';
import type { Location } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Globe, MapPin, Pencil, Phone, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    location: Location;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    delete: [id: number];
}>();

const page = usePage();
const messages = computed(() => page.props.messages as any);

function handleDelete() {
    emit('delete', props.location.id);
}
</script>

<template>
    <Card class="group transition-shadow hover:shadow-lg">
        <CardHeader>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <CardTitle class="text-lg">
                        {{ location.name }}
                    </CardTitle>
                    <p
                        v-if="location.description"
                        class="mt-1 line-clamp-2 text-sm text-muted-foreground"
                    >
                        {{ location.description }}
                    </p>
                </div>
                <Badge
                    :variant="location.is_active ? 'default' : 'secondary'"
                    class="ml-2"
                >
                    {{
                        location.is_active
                            ? messages.locations.fields.active
                            : messages.locations.fields.inactive
                    }}
                </Badge>
            </div>
        </CardHeader>
        <CardContent class="space-y-3">
            <div class="flex items-start gap-2 text-sm">
                <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                <span class="line-clamp-2">
                    {{ location.address }}, {{ location.city }},
                    {{ location.province }}
                </span>
            </div>
            <div v-if="location.phone" class="flex items-center gap-2 text-sm">
                <Phone class="h-4 w-4 shrink-0 text-muted-foreground" />
                <span>{{ location.phone }}</span>
            </div>
            <div
                v-if="location.country"
                class="flex items-center gap-2 text-sm"
            >
                <Globe class="h-4 w-4 shrink-0 text-muted-foreground" />
                <span>{{ location.country.name }}</span>
            </div>
        </CardContent>
        <CardFooter class="gap-2">
            <Button variant="outline" size="sm" class="flex-1" as-child>
                <Link :href="show(location.id)"> {{ messages.actions.show_details}} </Link>
            </Button>
            <Button variant="outline" size="icon" as-child>
                <Link :href="edit(location.id)">
                    <Pencil class="h-4 w-4" />
                </Link>
            </Button>
            <Button variant="destructive" size="icon" @click="handleDelete">
                <Trash2 class="h-4 w-4" />
            </Button>
        </CardFooter>
    </Card>
</template>
