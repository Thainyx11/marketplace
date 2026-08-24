<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateMarketplaceProfileForm extends Component
{
    use WithFileUploads;

    public string $bio = '';

    public string $shipping_address = '';

    public string $shop_name = '';

    public string $payout_note = '';

    public $avatar;

    public function mount(): void
    {
        $user = auth()->user();

        $this->bio = (string) $user->bio;
        $this->shipping_address = (string) $user->shipping_address;
        $this->shop_name = (string) $user->shop_name;
        $this->payout_note = (string) $user->payout_note;
    }

    public function save(): void
    {
        $rules = [
            'bio' => ['nullable', 'string', 'max:1000'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];

        if (auth()->user()->isVendeur()) {
            $rules['shop_name'] = ['required', 'string', 'max:255'];
            $rules['payout_note'] = ['nullable', 'string', 'max:500'];
        }

        $data = $this->validate($rules);

        $user = auth()->user();

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        // Boutiques créées directement par un administrateur (ou dont le nom
        // vient d'être renseigné pour la première fois) n'ont pas encore de
        // slug public — on en génère un dès que possible.
        if (($data['shop_name'] ?? null) && ! $user->shop_slug) {
            $data['shop_slug'] = Str::slug($data['shop_name']).'-'.Str::lower(Str::random(6));
        }

        $user->update($data);

        session()->flash('marketplace-profile-status', __('Profil mis à jour.'));
    }

    public function render()
    {
        return view('livewire.profile.update-marketplace-profile-form');
    }
}
