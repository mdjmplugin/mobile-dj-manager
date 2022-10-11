required_plugins = %w( vagrant-hostsupdater vagrant-host-shell )
required_plugins.each do |plugin|
  system "vagrant plugin install #{plugin}" unless Vagrant.has_plugin? plugin
end

puts "Removing local SSH keys from known_hosts file..."
input_lines = File.readlines("#{Dir.home}/.ssh/known_hosts")
input_lines.delete_if {|x| x.include? "127.0.0.1"}
File.open("#{Dir.home}/.ssh/known_hosts", 'w') do |f|
  input_lines.each do |line|
    f.write line
  end
end

# function to grab the hostname
def getHostName()
    ENV['domain'] = ''
    if File.file?('domain.txt')
        ENV['domain'] = File.read('domain.txt')
    end

    if ENV['domain'] == ''
        puts "Please enter a domain: "
        ENV['domain'] = URI.escape(STDIN.gets.chomp)
        File.open("domain.txt", "w") {|f| f.write(ENV['domain']) }
    end

    if ENV['domain'] == ''
        getHostName()
    else
       puts "Setting up VM with domain: "+ENV['domain']
    end
end

getHostName()

Vagrant.configure("2") do |config|

    config.vm.box = "bento/ubuntu-20.04"

    config.vm.network :private_network, ip: "192.168.56.1"
    config.vm.network :forwarded_port, guest: 443, host: 8443
    config.vm.network :forwarded_port, guest: 80, host: 8080
    config.vm.network :forwarded_port, guest: 3306, host: 3311

    config.vm.hostname = ENV['domain']

    config.vm.synced_folder ".", "/var/www/html/", id: "vagrant-root",
        owner: "vagrant",
        group: "www-data",
        mount_options: ["dmode=777,fmode=777"]

    config.vm.synced_folder ".ansible", "/vagrant", id: "ansible"

    config.vm.provider :virtualbox do |vb|
        vb.memory = 2048
        vb.cpus = 4
        vb.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/vagrant-root", "1"]
        vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]

    end

    config.vm.provision "shell", inline:
    "sudo sed -i 's/nameserver.*/nameserver 1.1.1.1/' /etc/resolv.conf"

    config.vm.provision "shell", inline:
    "sudo sed -i 's/PasswordAuthentication no/PasswordAuthentication yes/' /etc/ssh/sshd_config && service sshd restart"

    config.vm.provision "ansible_local" do |ansible|
        ansible.playbook = "playbooks/master.yml"
        ansible.extra_vars = { domain_input: ENV['domain'] }
    end
end
