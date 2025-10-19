<div>
    <!-- HEADER -->
    <x-header title="Cadastro de usuário" separator progress-indicator>
    </x-header>
    <!-- TABLE  -->
    <x-card>
        <x-steps wire:model="step" class=" my-5 p-5">
            <div>
                <x-form wire:submit="save">
                    <x-input label="Nome" wire:model="name" class=""/>
                    <x-input label="Email" wire:model="email" class=""/>

                    <x-select
                        label="Nivel de acesso"
                        :options="$roles"
                        option-value="id"
                        option-label="name"
                        placeholder="Selecionar"
                        placeholder-value=""
                        wire:model="roleSelect" class=""/>
                    <x-slot:actions>
                        <hr class="my-5"/>
                        <x-button wire:navigate href="{{ route('user.list')  }}" label="Cancelar"/>
                        <x-button label="Salvar" class="btn-primary" type="submit" spinner="save"/>
                    </x-slot:actions>
                </x-form>
            </div>
        </x-steps>
    </x-card>


</div>
