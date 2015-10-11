# -*- mode: ruby -*-
# vi: set ft=ruby :
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.ssh.forward_agent = true
    config.vm.box = "puppetlabs/ubuntu-14.04-64-puppet"
    config.vm.box_download_insecure = true
    config.vm.hostname = "php-cli.dev"
    config.vm.network :private_network, ip: "10.10.10.210"
    config.vm.synced_folder ".", "/vagrant", :nfs => { :mount_options => ["dmode=777","fmode=777"], :nfs_version => "3" }, id: "php-cli-root"
    config.vm.provider "virtualbox" do |vb|
        vb.customize ["modifyvm", :id, "--memory", "1536"]
        vb.name = "php-cli.dev"
    end

    config.vm.provision "shell" do |shell|
        shell.path = "provision/shell/init.sh"
    end
end
